<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use App\Support\Pagination\IPaginate;

interface TransactionRepositoryInterface
{
    public function findById(int $id): ?Transaction;

    public function findOrFail(int $id): Transaction;

    public function getPaginatedForAccount(int $accountId, int $index, int $size, int $from = 0, array $filters = []): IPaginate;

    public function createWithdrawal(int $accountId, float $amount, float $balanceBefore, float $balanceAfter, array $dispensedNotes): Transaction;

    public function createFailedWithdrawal(int $accountId, float $amount, float $balanceBefore, string $reason): Transaction;

    public function delete(int $id): bool;
}
