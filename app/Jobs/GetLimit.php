<?php

namespace App\Listeners;

use App\Events\StatisticUpdated;
use App\Models\Statistic;
use App\Repositories\MachineLearningFacade;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GetLimit implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $userId,
        public DateTime $date,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MachineLearningFacade $machineLearningFacade): void
    {
        $currentYear = Carbon::now()->format('Y');
        $currentMonth = Carbon::now()->format('m');
        $currentUserId = $this->userId;

        $year = $this->date->format('Y');
        $month = $this->date->format('m');

        $previousStatistic = Statistic::where('user_id', $currentUserId)
            ->where('year', $year)
            ->where('month', $month - 1)
            ->first();

        while ($month <= $currentMonth && $year <= $currentYear) {
            $statistic = Statistic::where('user_id', $currentUserId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if ($statistic) {
                $ratio = $machineLearningFacade->getLimit([
                    'total_income' => $statistic->total_income,
                    'total_installment' => $statistic->total_installment,
                    'personality' => $statistic->personality,
                    'last_month_limit', $previousStatistic->ratio ?? 0.15,
                ]);

                $statistic->fill([
                    'ratio' => $ratio,
                ]);
                $statistic->save();
                $previousStatistic = $statistic;

                broadcast(new StatisticUpdated($statistic));
            } else {
                break;
            }

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }
}
