<?php

namespace App\Repositories\Contracts;

use App\Models\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function findOrFail(int $id): Account;

    public function findByUserId(int $userId): ?Account;

    public function findByUserIdOrFail(int $userId): Account;

    public function decrementBalance(int $accountId, float $amount): ?Account;
    
    public function create(array $data): Account;
}