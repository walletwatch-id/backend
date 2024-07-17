<?php

namespace App\Repositories;

use DateTime;

interface TransactionRepository
{
    public function getInstallmentsOnMonth(string $userId, DateTime $date): int;

    public function getTransactionsOnRange(string $userId, DateTime $startDate, DateTime $endDate): int;
}
