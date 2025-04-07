<?php

namespace App\Repositories\Eloquent;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Exceptions\{ResourceNotFoundException, InsufficientFundsException};
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function findOrFail(int $id): Account
    {
        try {
            return Account::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ResourceNotFoundException("Account with ID {$id} not found.", $e->getCode(), $e);
        }
    }

    public function findByUserId(int $userId): ?Account
    {
        return Account::where('user_id', $userId)->first();
    }

    public function findByUserIdOrFail(int $userId): Account
    {
        $account = $this->findByUserId($userId);
        if (!$account) {
            throw new ResourceNotFoundException("Account for user ID {$userId} not found.");
        }
        return $account;
    }

    public function decrementBalance(int $accountId, float $amount): Account
    {
        $affectedRows = Account::where('id', $accountId)->where('balance', '>=', $amount)->decrement('balance', $amount);

        if ($affectedRows === 0) {
            $accountExists = Account::where('id', $accountId)->exists();

            if (!$accountExists) {
                throw new ResourceNotFoundException("Account with ID {$accountId} not found during balance decrement attempt.");
            } else {
                throw new InsufficientFundsException("Insufficient funds for account ID {$accountId} to decrement by {$amount}.");
            }
        }
        return $this->findOrFail($accountId);
    }

    public function create(array $data): Account
    {
        return Account::create($data);
    }
}
