<?php

namespace App\Services\WithdrawalService;

use App\Models\Account;

interface WithdrawalServiceInterface
{
    public function attemptWithdrawal(Account $account, float $amount): array;
}
