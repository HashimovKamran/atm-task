<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\OperationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\StoreAccountRequest;
use App\Http\Resources\Api\V1\AccountResource;
use App\Services\AccountService\AccountServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    public function __construct(protected AccountServiceInterface $accountService) {}

    public function store(StoreAccountRequest $request): AccountResource|JsonResponse
    {
        $validatedData = $request->validated();

        $account = $this->accountService->createAccount($validatedData);

        if (!$account)
            throw new OperationFailedException('Failed to create the account.');

        return (new AccountResource($account))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
