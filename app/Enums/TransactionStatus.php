<?php

namespace App\Enums;

enum TransactionStatus: int
{
    case Completed = 1;
    case Pending = 2;
    case Failed = 3;

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Pending => 'Pending',
            self::Failed => 'Failed',
        };
    }

    public static function tryFromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}