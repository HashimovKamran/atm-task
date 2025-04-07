<?php

namespace App\Services\AccountService;

use App\Models\{User, Account};
use App\Repositories\Contracts\{AccountRepositoryInterface, TransactionRepositoryInterface};
use App\Services\AccountService\AccountServiceInterface;
use App\Support\Pagination\IPaginate;
use Illuminate\Support\Facades\Hash;

class AccountManager implements AccountServiceInterface
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepository,
        protected TransactionRepositoryInterface $transactionRepository
    ) {}

    public function createAccount(array $accountData): Account
    {
        $preparedData = $this->prepareAccountData($accountData);
        return $this->accountRepository->create($preparedData);
    }

    public function getAccountByUser(User $user): Account
    {
        return $this->accountRepository->findByUserIdOrFail($user->id);
    }

    public function getAccountByIdOrFail(int $accountId): Account
    {
        return $this->accountRepository->findOrFail($accountId);
    }

    public function getTransactionHistory(Account $account, int $index, int $size, int $from = 0, array $filters = []): IPaginate
    {
        return $this->transactionRepository->getPaginatedForAccount($account->id, $index, $size, $from, $filters);
    }

    protected function prepareAccountData(array $data): array
    {
        if (isset($data['pin']) && !is_null($data['pin'])) {
            $data['pin'] = Hash::make($data['pin']);
        } else {
            unset($data['pin']);
        }

        if (isset($data['card_number']) && is_null($data['card_number']))
            unset($data['card_number']);

        if (isset($data['user_id']) && is_null($data['user_id']))
            unset($data['user_id']);
        return $data;
    }
}
