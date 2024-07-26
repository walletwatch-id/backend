<?php

namespace App\Listeners;

use App\Events\StatisticCreated;
use App\Events\StatisticUpdated;
use App\Models\Statistic;
use App\Models\SurveyResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GetTotalIncome implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
    public function handle(): void
    {
        $surveyQuestionsCount = $this->surveyResult->survey()->withCount('surveyQuestions')
            ->get();

        $surveyAnswers = $this->surveyResult->surveyResultAnswers()
            ->orderBy('question_id', 'asc')
            ->get()
            ->toArray();

        if (count($surveyAnswers) === $surveyQuestionsCount) {
            $totalIncome = (int) $surveyAnswers[0]['answer'];

            $currentYear = Carbon::now()->format('Y');
            $currentMonth = Carbon::now()->format('m');
            $currentUserId = $this->surveyResult->user_id;

            $year = $this->surveyResult->date->format('Y');
            $month = $this->surveyResult->date->format('m');

            $previousIncome = Statistic::where('user_id', $currentUserId)
                ->where('year', $year)
                ->where('month', $month)
                ->first()
                ->total_income ?? 0;

            while ($month <= $currentMonth && $year <= $currentYear) {
                $statistic = Statistic::where('user_id', $currentUserId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if ($statistic) {
                    if ($statistic->total_income === $previousIncome) {
                        $statistic->fill([
                            'total_income' => $totalIncome,
                        ]);
                        $statistic->save();

                        broadcast(new StatisticUpdated($statistic));
                    } else {
                        break;
                    }
                } else {
                    $statistic = new Statistic([
                        'user_id' => $currentUserId,
                        'year' => $year,
                        'month' => $month,
                        'personality' => '',
                        'total_transaction' => 0,
                        'total_installment' => 0,
                        'total_income' => $totalIncome,
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
        }
    }
}
