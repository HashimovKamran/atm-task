<?php

namespace App\Support\Pagination;

interface IPaginate
{
    public function getFrom(): int;
    public function getIndex(): int;
    public function getSize(): int;
    public function getCount(): int;
    public function getPages(): int;
    public function getItems(): array;
    public function hasPrevious(): bool;
    public function hasNext(): bool;
    public function toArray(): array;
}
