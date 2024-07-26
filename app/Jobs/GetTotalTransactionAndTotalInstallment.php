<?php

namespace App\Listeners;

use App\Events\StatisticCreated;
use App\Events\StatisticUpdated;
use App\Models\Statistic;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GetTotalTransactionAndTotalInstallment implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Transaction $transaction,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TransactionRepository $transactionRepository): void
    {
        $currentYear = Carbon::now()->format('Y');
        $currentMonth = Carbon::now()->format('m');
        $currentUserId = $this->transaction->user_id;

        $year = $this->transaction->transaction_datetime->format('Y');
        $month = $this->transaction->transaction_datetime->format('m');

        while ($month <= $currentMonth && $year <= $currentYear) {
            $firstDate = Carbon::create($year, $month, 1, 0, 0, 0);
            $lastDate = $firstDate->copy()->endOfMonth();

            $totalTransaction = $transactionRepository->getTransactionsOnRange($currentUserId, $firstDate, $lastDate);
            $totalInstallment = $transactionRepository->getInstallmentsOnMonth($currentUserId, $lastDate);

            $statistic = Statistic::where('user_id', $currentUserId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if ($statistic) {
                $statistic->fill([
                    'total_transaction' => $totalTransaction,
                    'total_installment' => $totalInstallment,
                ]);

                $statistic->save();

                broadcast(new StatisticUpdated($statistic));
            } else {
                $statistic = new Statistic([
                    'user_id' => $currentUserId,
                    'year' => $year,
                    'month' => $month,
                    'personality' => '',
                    'total_transaction' => $totalTransaction,
                    'total_installment' => $totalInstallment,
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
    }
}
