<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\{AccountResource, TransactionCollection};
use App\Services\AccountService\AccountServiceInterface;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\Account\TransactionHistoryRequest;
use App\Traits\TargetAccountResolver;
use Illuminate\Support\Arr;

class AccountController extends Controller
{
    use TargetAccountResolver;

    public function __construct(protected AccountServiceInterface $accountService) {}

    public function show(Request $request): AccountResource
    {
        $accountId = $this->getTargetAccountId($request, $this->accountService);
        $account = $this->accountService->getAccountByIdOrFail($accountId);
        return new AccountResource($account);
    }

    public function transactions(TransactionHistoryRequest $request): TransactionCollection
    {
        $accountId = $this->getTargetAccountId($request, $this->accountService);
        $account = $this->accountService->getAccountByIdOrFail($accountId);

        $validatedData = $request->validated();
        $index = $validatedData['index'] ?? 0;
        $size = $validatedData['size'] ?? 15;
        $from = $validatedData['from'] ?? 0;
        $filters = Arr::only($validatedData, ['type', 'start_date', 'end_date']);

        $paginatedResult = $this->accountService->getTransactionHistory(
            $account,
            $index,
            $size,
            $from,
            $filters
        );

        return new TransactionCollection($paginatedResult);
    }
}
