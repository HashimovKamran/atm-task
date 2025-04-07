<?php

namespace App\Services\TransactionService;

use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionManager implements TransactionServiceInterface
{
    public function __construct(protected TransactionRepositoryInterface $transactionRepository) {}

    public function delete(int $id): bool
    {
        return $this->transactionRepository->delete($id);
    }
}
