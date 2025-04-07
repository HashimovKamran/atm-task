<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\WithdrawalRequest;
use App\Http\Resources\Api\V1\WithdrawalResource;
use App\Services\AccountService\AccountServiceInterface;
use App\Services\WithdrawalService\WithdrawalServiceInterface;
use App\Traits\TargetAccountResolver;

class WithdrawalController extends Controller
{
    use TargetAccountResolver;

    public function __construct(
        protected WithdrawalServiceInterface $withdrawalService,
        protected AccountServiceInterface $accountService
    ) {}

    public function store(WithdrawalRequest $request): WithdrawalResource
    {
        $accountId = $this->getTargetAccountId($request, $this->accountService);
        $account = $this->accountService->getAccountByIdOrFail($accountId);
        $amount = (float) $request->validated('amount');
        $withdrawalDetails = $this->withdrawalService->attemptWithdrawal($account, $amount);
        return new WithdrawalResource($withdrawalDetails);
    }
}
