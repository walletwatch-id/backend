<?php

namespace App\Jobs;

use App\Events\StatisticCreated;
use App\Events\StatisticUpdated;
use App\Models\Statistic;
use App\Models\SurveyResult;
use App\Repositories\MachineLearningFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GetPersonality implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SurveyResult $surveyResult,
    ) {}

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->surveyResult->id;
    }

    /**
     * Execute the job.
     */
    public function handle(MachineLearningFacade $machineLearningFacade): void
    {
        $surveyQuestionsCount = $this->surveyResult
            ->survey()
            ->withCount('surveyQuestions')
            ->first()
            ->survey_questions_count;

        $surveyAnswers = $this->surveyResult
            ->surveyAnswers()
            ->orderBy('question_id', 'asc')
            ->get()
            ->toArray();

        if (count($surveyAnswers) === $surveyQuestionsCount) {
            $features = [];
            for ($i = 0; $i < 36; $i++) {
                $features['f'.str_pad($i + 1, 2, '0', STR_PAD_LEFT)] = (int) $surveyAnswers[$i]['answer'];
            }

            $personality = $machineLearningFacade->getPersonality($features);

            $currentYear = Carbon::now()->format('Y');
            $currentMonth = Carbon::now()->format('m');
            $currentUserId = $this->surveyResult->user_id;

            $year = $this->surveyResult->date->format('Y');
            $month = $this->surveyResult->date->format('m');

            while ($month <= $currentMonth && $year <= $currentYear) {
                $statistic = Statistic::where('user_id', $currentUserId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if ($statistic) {
                    $statistic->fill([
                        'personality' => $personality,
                    ]);
                    $statistic->save();

                    broadcast(new StatisticUpdated($statistic));
                } else {
                    $statistic = new Statistic([
                        'user_id' => $currentUserId,
                        'year' => $year,
                        'month' => $month,
                        'personality' => $personality,
                        'total_transaction' => 0,
                        'total_installment' => 0,
                        'total_income' => 0,
                        'ratio' => 0,
                    ]);
                    $statistic->save();

                    broadcast(new StatisticCreated($statistic));
                }

                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
            }

            dispatch(new GetLimit($currentUserId, $this->surveyResult->date));
        }
    }
}
