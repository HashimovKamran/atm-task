<?php

namespace App\Enums;

enum TransactionType: int
{
    case Withdrawal = 1;
    case Deposit = 2;
    case FailedWithdrawal = 3;

    public function label(): string
    {
        return match ($this) {
            self::Withdrawal => 'Withdrawal',
            self::Deposit => 'Deposit',
            self::FailedWithdrawal => 'Failed Withdrawal',
        };
    }
    
    public static function tryFromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}