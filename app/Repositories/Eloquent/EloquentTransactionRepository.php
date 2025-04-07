<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Support\Pagination\{IPaginate, Paginate};
use App\Enums\{TransactionType, TransactionStatus};
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findOrFail(int $id): Transaction
    {
        try {
            return Transaction::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ResourceNotFoundException("Transaction with ID {$id} not found.", $e->getCode(), $e);
        }
    }

    public function getPaginatedForAccount(
        int $accountId,
        int $index,
        int $size,
        int $from = 0,
        array $filters = []
    ): IPaginate {
        $query = Transaction::query()->where('account_id', $accountId)->latest('transaction_time');

        if (!empty($filters['type'])) {
            $query = $query->where('type', $filters['type']);
        }
        if (!empty($filters['start_date'])) {
            $query = $query->whereDate('transaction_time', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query = $query->whereDate('transaction_time', '<=', $filters['end_date']);
        }

        return new Paginate($query, $index, $size, $from);
    }

    public function createWithdrawal(
        int $accountId,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        array $dispensedNotes
    ): Transaction {
        return Transaction::create([
            'account_id' => $accountId,
            'type' => TransactionType::Withdrawal,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'dispensed_notes' => $dispensedNotes,
            'status' => TransactionStatus::Completed,
            'failure_reason' => null,
            'transaction_time' => now(),
        ]);
    }

    public function createFailedWithdrawal(
        int $accountId,
        float $amount,
        float $balanceBefore,
        string $reason
    ): Transaction {
        return Transaction::create([
            'account_id' => $accountId,
            'type' => TransactionType::FailedWithdrawal,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore,
            'dispensed_notes' => null,
            'status' => TransactionStatus::Failed,
            'failure_reason' => $reason,
            'transaction_time' => now(),
        ]);
    }

    public function delete(int $id): bool
    {
        $transaction = $this->findById($id);
        if ($transaction)
            return $transaction->delete();
        return false;
    }
}
