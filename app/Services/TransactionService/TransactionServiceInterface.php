<?php

namespace App\Services\TransactionService;

interface TransactionServiceInterface
{
    public function delete(int $id): bool;
}
