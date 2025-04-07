<?php

namespace App\Services\WithdrawalService;

use App\Models\Account;
use App\Repositories\Contracts\{AccountRepositoryInterface, BanknoteRepositoryInterface, TransactionRepositoryInterface};
use App\Services\WithdrawalService\WithdrawalServiceInterface;
use Illuminate\Support\Facades\{DB, Log};
use App\Exceptions\{InsufficientFundsException, CannotDispenseAmountException, WithdrawalLimitException};
use App\Models\Banknote;
use Illuminate\Support\Collection;
use Throwable;

class WithdrawalManager implements WithdrawalServiceInterface
{
    protected array $denominationPreference;
    protected float $minWithdrawal;
    protected float $maxWithdrawal;
    protected float $smallestUnit;
    protected string $currency;

    public function __construct(
        protected AccountRepositoryInterface $accountRepository,
        protected TransactionRepositoryInterface $transactionRepository,
        protected BanknoteRepositoryInterface $banknoteRepository
    ) {
        $this->currency = config('atm.currency', 'AZN');
        $this->denominationPreference = config('atm.denominations_preference');
        $this->minWithdrawal = (float) config('atm.min_withdrawal');
        $this->maxWithdrawal = (float) config('atm.max_withdrawal_per_tx');
        $this->smallestUnit = (float) config('atm.smallest_dispensable_unit');
    }

    public function attemptWithdrawal(Account $account, float $amount): array
    {
        $this->validateWithdrawalRequest($account, $amount);

        return DB::transaction(function () use ($account, $amount) {
            $lockedAccount = Account::lockForUpdate()->find($account->id);
            if (!$lockedAccount || $lockedAccount->balance < $amount) {
                $this->logFailedTransaction($account, $amount, 'Insufficient funds during transaction lock.');
                throw new InsufficientFundsException('Insufficient funds detected during transaction.');
            }

            $lockedBanknotes = Banknote::where('currency', $this->currency)->lockForUpdate()->get()->keyBy('denomination');

            $dispensedNotes = $this->calculateDispenseWithLockedBanknotes($amount, $lockedBanknotes);
            if (empty($dispensedNotes)) {
                $this->logFailedTransaction($account, $amount, 'Cannot dispense exact amount with available banknotes.');
                throw new CannotDispenseAmountException('Cannot dispense the requested amount with currently available banknotes.');
            }

            foreach ($dispensedNotes as $denomination => $count) {
                if ($count > 0) {
                    $banknote = $lockedBanknotes[$denomination];
                    $banknote->count -= $count;
                    $banknote->save();
                }
            }

            $originalBalance = $lockedAccount->balance;
            $lockedAccount->balance -= $amount;
            $lockedAccount->save();

            $transaction = $this->transactionRepository->createWithdrawal(
                $lockedAccount->id,
                $amount,
                $originalBalance,
                $lockedAccount->balance,
                $dispensedNotes
            );

            return [
                'transaction_id' => $transaction->id,
                'amount_withdrawn' => $amount,
                'dispensed_notes' => $dispensedNotes,
                'new_balance' => (float) $lockedAccount->balance,
                'timestamp' => $transaction->transaction_time,
            ];
        });
    }

    protected function validateWithdrawalRequest(Account $account, float $amount): void
    {
        if ($amount < $this->minWithdrawal) {
            throw new CannotDispenseAmountException("Withdrawal amount must be at least {$this->minWithdrawal} {$this->currency}.");
        }
        if ($amount > $this->maxWithdrawal) {
            throw new WithdrawalLimitException("Withdrawal amount cannot exceed {$this->maxWithdrawal} {$this->currency} per transaction.");
        }
        if (round(fmod($amount, $this->smallestUnit), 5) != 0) { // Use rounding for float precision
            throw new CannotDispenseAmountException("Withdrawal amount must be a multiple of {$this->smallestUnit} {$this->currency}.");
        }
        // Initial balance check (will be re-checked with lock in transaction)
        if ($account->balance < $amount) {
            $this->logFailedTransaction($account, $amount, 'Insufficient funds (initial check).');
            throw new InsufficientFundsException();
        }
    }

    protected function calculateDispenseWithLockedBanknotes(float $amount, Collection $lockedBanknotes): array
    {
        $dispensed = [];
        $remainingAmount = $amount;

        foreach ($this->denominationPreference as $denomination) {
            if ($remainingAmount <= 0) break;

            if (isset($lockedBanknotes[$denomination])) {
                $banknote = $lockedBanknotes[$denomination];
                if ($banknote->is_available && $banknote->count > 0) {
                    $maxPossibleCount = floor($remainingAmount / $denomination);
                    $countToTake = min($maxPossibleCount, $banknote->count);
                    if ($countToTake > 0) {
                        $dispensed[$denomination] = (int) $countToTake;
                        $remainingAmount -= $countToTake * $denomination;
                        $remainingAmount = round($remainingAmount, 5);
                    }
                }
            }
        }

        return $remainingAmount == 0 ? $dispensed : [];
    }

    protected function logFailedTransaction(Account $account, float $amount, string $reason): void
    {
        try {
            $this->transactionRepository->createFailedWithdrawal(
                $account->id,
                $amount,
                $account->balance,
                $reason
            );
        } catch (Throwable $e) {
            Log::error("Failed to log failed withdrawal transaction: " . $e->getMessage(), [
                'account_id' => $account->id,
                'amount' => $amount,
                'reason' => $reason
            ]);
        }
    }
}
