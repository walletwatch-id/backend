<?php

namespace App\Repositories;

use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionRepositoryImpl implements TransactionRepository
{
    /**
     * Get the total installments on a month.
     */
    public function getInstallmentsOnMonth(string $userId, DateTime $date): int
    {
        $dateString = Carbon::instance($date)->startOfMonth()->format('Y-m-d H:i:s');

        return DB::table('transactions')
            ->where('user_id', $userId)
            ->whereRaw('first_installment_datetime <= ?', [$dateString])
            ->whereRaw('first_installment_datetime + (period - 1) * interval \'1 month\' >= ?', [$dateString])
            ->sum('monthly_installment');
    }

    /**
     * Get the total transactions on a range.
     */
    public function getTransactionsOnRange(string $userId, DateTime $startDate, DateTime $endDate): int
    {
        $startDate = $startDate->format('Y-m-d H:i:s');
        $endDate = $endDate->format('Y-m-d H:i:s');

        return DB::table('transactions')
            ->where('user_id', $userId)
            ->whereBetween('transaction_datetime', [$startDate, $endDate])
            ->sum(DB::raw('monthly_installment * period'));
    }
}
