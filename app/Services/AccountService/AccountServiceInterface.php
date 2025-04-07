<?php

namespace App\Services\AccountService;

use App\Models\{User, Account};
use App\Support\Pagination\IPaginate;

interface AccountServiceInterface
{
    public function getAccountByUser(User $user): Account;

    public function getAccountByIdOrFail(int $accountId): Account;

    public function getTransactionHistory(Account $account, int $index, int $size, int $from = 0, array $filters = []): IPaginate;

    public function createAccount(array $accountData): Account;
}
