<?php

namespace App\Support\Pagination;

abstract class BasePageableModel
{
    public int $index;
    public int $size;
    public int $count;
    public int $pages;
    public bool $hasPrevious;
    public bool $hasNext;
}
