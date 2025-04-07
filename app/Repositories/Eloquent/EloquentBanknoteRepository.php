<?php

namespace App\Repositories\Eloquent;

use App\Models\Banknote;
use App\Repositories\Contracts\BanknoteRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentBanknoteRepository implements BanknoteRepositoryInterface
{
    public function findByDenomination(int $denomination, string $currency = 'AZN'): ?Banknote
    {
        return Banknote::where('denomination', $denomination)->where('currency', $currency)->first();
    }

    public function getAll(string $currency = 'AZN'): Collection
    {
        return Banknote::where('currency', $currency)->orderBy('denomination', 'desc')->get();
    }

    public function getAvailableBanknotes(string $currency = 'AZN'): Collection
    {
        return Banknote::where('currency', $currency)
            ->where('is_available', true)
            ->where('count', '>', 0)
            ->orderBy('denomination', 'desc')
            ->get();
    }

    public function hasEnough(int $denomination, int $requiredCount, string $currency = 'AZN'): bool
    {
        $banknote = Banknote::where('denomination', $denomination)->where('currency', $currency)->lockForUpdate()->first();
        return $banknote && $banknote->is_available && $banknote->count >= $requiredCount;
    }

    public function decrementCount(int $denomination, int $countToDecrement, string $currency = 'AZN'): bool
    {
        $affected = Banknote::where('denomination', $denomination)
            ->where('currency', $currency)
            ->where('count', '>=', $countToDecrement)
            ->decrement('count', $countToDecrement);

        return $affected > 0;
    }

    public function incrementCount(int $denomination, int $countToIncrement, string $currency = 'AZN'): bool
    {
        $banknote = Banknote::where('denomination', $denomination)->where('currency', $currency)->lockForUpdate()->first();
        return $banknote ? Banknote::where('id', $banknote->id)->increment('count', $countToIncrement) > 0 : false;
    }
}
