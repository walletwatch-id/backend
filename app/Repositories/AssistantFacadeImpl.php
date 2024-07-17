<?php

namespace App\Repositories;

use App\Events\ChatMessageCreated;
use App\Events\ChatMessageUpdated;
use App\Models\ChatMessage;
use Exception;
use Illuminate\Support\Carbon;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\StreamResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use OpenAI\Responses\Threads\ThreadResponse;

class AssistantFacadeImpl implements AssistantFacade
{
    protected bool $isDebug = false;

    protected string $assistantId;

    public function __construct(
        protected TransactionRepository $transactionRepository,
    ) {
        $this->assistantId = config('openai.assistant');
        $this->isDebug = config('app.debug');
    }

    private function retrieveThread(string $threadId): ThreadResponse
    {
        return OpenAI::threads()->retrieve($threadId);
    }

    private function createThread(array $parameters): ThreadResponse
    {
        return OpenAI::threads()->create($parameters);
    }

    private function listMessages(string $threadId, array $parameters): ThreadMessageListResponse
    {
        return OpenAI::threads()->messages()->list($threadId, $parameters);
    }

    private function createMessage(string $threadId, array $parameters): ThreadMessageResponse
    {
        return OpenAI::threads()->messages()->create($threadId, $parameters);
    }

    private function modifyMessage(string $threadId, string $messageId, array $parameters): ThreadMessageResponse
    {
        return OpenAI::threads()->messages()->modify($threadId, $messageId, $parameters);
    }

    private function deleteMessage(string $threadId, string $messageId): void
    {
        OpenAI::threads()->messages()->delete($threadId, $messageId);
    }

    private function createStreamedRun(string $threadId, array $parameters): StreamResponse
    {
        return OpenAI::threads()->runs()->createStreamed($threadId, $parameters);
    }

    private function submitToolOutputs(string $threadId, string $runId, array $parameters): ThreadRunResponse
    {
        return OpenAI::threads()->runs()->submitToolOutputs($threadId, $runId, $parameters);
    }

    private function submitToolOutputsStreamed(string $threadId, string $runId, array $parameters): StreamResponse
    {
        return OpenAI::threads()->runs()->submitToolOutputsStreamed($threadId, $runId, $parameters);
    }

    private function mapToolCallToFunctions($toolCall, string $userId)
    {
        $name = $toolCall->function->name;
        $arguments = json_decode($toolCall->function->arguments);

        switch ($name) {
            case 'get_installments':
                $date = Carbon::parse($arguments?->month) ?? Carbon::now();

                return strval($this->transactionRepository->getInstallmentsOnMonth($userId, $date));
            case 'get_transactions':
                $startDate = Carbon::parse($arguments?->start_date) ?? Carbon::now()->startOfMonth();
                $endDate = Carbon::parse($arguments?->end_date) ?? Carbon::now()->endOfMonth();

                return strval($this->transactionRepository->getTransactionsOnRange($userId, $startDate, $endDate));
            default:
                return '';
        }
    }

    private function summarizeMessages(string $threadId, string $after, string $order = 'asc'): array
    {
        $params = [
            'order' => $order,
            'limit' => 20,
            'after' => $after,
        ];
        $messages = $this->listMessages($threadId, $params)->data;
        $messageId = $messages[0]->id;
        $count = count($messages);
        $summary = '';

        // Check if assistant sent more than 1 message
        if ($count > 1) {
            foreach ($messages as $message) {
                // Concatenate multiple messages
                $summary .= $message->content[0]->text->value."\n\n";
            }

            // Remove the last new line
            $summary = rtrim($summary);
        } else {
            // Take the first message
            $summary = $messages[0]->content[0]->text->value;
        }

        return [$messageId, $summary];
    }

    private function handleThreadRunResponseRequiredAction(ThreadRunResponse $response, bool $streamed, string $userId): ThreadRunResponse|StreamResponse
    {
        $toolCalls = $response->requiredAction->submitToolOutputs->toolCalls;
        $toolOutputs = [];

        foreach ($toolCalls as $toolCall) {
            $output = $this->mapToolCallToFunctions($toolCall, $userId);

            $toolOutputs[] = [
                'tool_call_id' => $toolCall->id,
                'output' => $output,
            ];
        }

        if ($streamed) {
            return $this->submitToolOutputsStreamed(
                threadId: $response->threadId,
                runId: $response->id,
                parameters: [
                    'tool_outputs' => $toolOutputs,
                ]
            );
        } else {
            return $this->submitToolOutputs(
                threadId: $response->threadId,
                runId: $response->id,
                parameters: [
                    'tool_outputs' => $toolOutputs,
                ]
            );
        }
    }

    private function handleStreamedRunResponse(StreamResponse $stream, string $userId, ChatMessage $response): ThreadRunResponse
    {
        $run = null;

        do {
            foreach ($stream as $chunk) {
                switch ($chunk->event) {
                    case 'thread.run.created':
                        $run = $chunk->response;
                        $response->status = 'CREATED';
                        $response->save();

                        broadcast(new ChatMessageCreated($response));

                        break;
                    case 'thread.run.queued':
                        $run = $chunk->response;
                        $response->status = 'QUEUED';
                        $response->save();

                        broadcast(new ChatMessageUpdated($response));

                        break;
                    case 'thread.run.in_progress':
                        $run = $chunk->response;
                        $response->status = 'IN_PROGRESS';
                        $response->save();

                        broadcast(new ChatMessageUpdated($response));

                        break;
                    case 'thread.run.requires_action':
                        $run = $chunk->response;
                        // Overwrite the responses with the new responses started by submitting the tool outputs
                        if ($run->requiredAction->type === 'submit_tool_outputs') {
                            if ($this->isDebug) {
                                Log::info('Assistant requires action to submit tool outputs.', [
                                    'tool_calls' => $run->requiredAction->submitToolOutputs->toolCalls->toArray(),
                                ]);
                            }

                            $stream = $this->handleThreadRunResponseRequiredAction($run, true, $userId);
                        }
                        break;
                    case 'thread.run.completed':
                        $run = $chunk->response;
                        break;
                    case 'thread.run.cancelled':
                        $run = $chunk->response;
                        break 3;
                    case 'thread.run.failed':
                        $run = $chunk->response;
                        break 3;
                    case 'thread.run.incomplete':
                        $run = $chunk->response;
                        break 3;
                    case 'thread.run.expired':
                        $run = $chunk->response;
                        break 3;
                }
            }
        } while ($run->status !== 'completed');

        return $run;
    }

    public function generateResponse(ChatMessage $chatMessage): ChatMessage
    {
        $chatMessage->refresh();
        $chatSession = $chatMessage->chatSession;
        $isNewMessage = $chatMessage->external_id === null;
        $isNewSession = $chatSession->external_id === null;

        $thread = null;
        $message = null;

        if (! $isNewSession) {
            try {
                $thread = $this->retrieveThread($chatSession->external_id ?? '');
            } catch (Exception $e) {
                $isNewSession = true;
                $thread = $this->createThread([]);
                $chatSession->external_id = $thread->id;
                $chatSession->save();
            }
        } else {
            $thread = $this->createThread([]);
            $chatSession->external_id = $thread->id;
            $chatSession->save();
        }

        if ((! $isNewSession) && (! $isNewMessage)) {
            try {
                $oldResponseMessages = $this->listMessages($thread->id, [
                    'order' => 'asc',
                    'limit' => 100,
                    'after' => $chatMessage->external_id,
                ])->data;

                foreach ($oldResponseMessages as $oldResponseMessage) {
                    $this->deleteMessage($thread->id, $oldResponseMessage->id);
                }

                $message = $this->modifyMessage($thread->id, $chatMessage->external_id, [
                    'role' => 'user',
                    'content' => $chatMessage->message,
                ]);
            } catch (Exception $e) {
                $message = $this->createMessage($thread->id, [
                    'role' => 'user',
                    'content' => $chatMessage->message,
                ]);
                $chatMessage->external_id = $message->id;
                $chatMessage->save();
            }
        } else {
            $message = $this->createMessage($thread->id, [
                'role' => 'user',
                'content' => $chatMessage->message,
            ]);
            $chatMessage->external_id = $message->id;
            $chatMessage->save();
        }

        $stream = $this->createStreamedRun($thread->id, [
            'assistant_id' => $this->assistantId,
            'additional_instructions' => implode('. ', [
                'Additional context if required: current date is '.Carbon::now()->format('Y-m-d').', '.
                'current user name is '.$chatSession->user->name,
            ]).'.',
        ]);

        $response = new ChatMessage([
            'session_id' => $chatSession->id,
            'sender' => 'BOT',
            'message' => '',
        ]);

        $run = $this->handleStreamedRunResponse($stream, $chatSession->user_id, $response);

        switch ($run->status) {
            case 'completed':
                [$messageId, $summary] = $this->summarizeMessages($thread->id, $message->id);
                $response->external_id = $messageId;
                $response->status = 'COMPLETED';
                $response->message = $summary;
                $response->hash = md5($summary);
                $response->save();

                broadcast(new ChatMessageUpdated($response));

                break;
            case 'cancelled':
                $response->status = 'CANCELLED';
                $response->save();

                broadcast(new ChatMessageUpdated($response));

                break;
            case 'failed':
                $response->status = 'FAILED';
                $response->save();

                if ($this->isDebug) {
                    Log::warning('Assistant failed to generate response.', [
                        'last_error' => $run->lastError->toArray(),
                    ]);
                }

                broadcast(new ChatMessageUpdated($response));

                break;
            case 'incomplete':
                [$messageId, $summary] = $this->summarizeMessages($thread->id, $message->id);
                $response->external_id = $messageId;
                $response->status = 'INCOMPLETE';
                $response->message = $summary;
                $response->hash = md5($summary);
                $response->save();

                broadcast(new ChatMessageUpdated($response));

                break;
            case 'expired':
                $response->status = 'EXPIRED';
                $response->save();

                broadcast(new ChatMessageUpdated($response));

                break;
        }

        return $response;
    }
}
