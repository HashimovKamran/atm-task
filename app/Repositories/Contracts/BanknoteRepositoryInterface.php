<?php

namespace App\Repositories\Contracts;

use App\Models\Banknote;
use Illuminate\Support\Collection;

interface BanknoteRepositoryInterface
{
    public function findByDenomination(int $denomination, string $currency = 'AZN'): ?Banknote;

    public function getAvailableBanknotes(string $currency = 'AZN'): Collection;

    public function hasEnough(int $denomination, int $requiredCount, string $currency = 'AZN'): bool;

    public function decrementCount(int $denomination, int $countToDecrement, string $currency = 'AZN'): bool;

    public function incrementCount(int $denomination, int $countToIncrement, string $currency = 'AZN'): bool;
    
    public function getAll(string $currency = 'AZN'): Collection;
}