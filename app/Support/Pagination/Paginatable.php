<?php

namespace App\Support\Pagination;

trait Paginatable
{
    public function scopeToPaginate(iterable $query, int $index, int $size, int $from = 0): Paginate
    {
        return new Paginate($query, $index, $size, $from);
    }
}
