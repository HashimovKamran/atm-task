<?php

namespace App\Support\Pagination;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class Paginate implements IPaginate
{
    public int $from;
    public int $index;
    public int $size;
    public int $count;
    public int $pages;
    public Collection $items;

    public function __construct($source, int $index, int $size, int $from = 0)
    {
        $this->index = $index;
        $this->size = $size;
        $this->from = $from;

        if ($source instanceof Builder) {
            $this->count = $source->count();
            $this->items = $source->skip(($index - $from) * $size)->take($size)->get();
        } else {
            $collection = $source instanceof Collection ? $source : collect($source);
            $this->count = $collection->count();
            $this->items = $collection->skip(($index - $from) * $size)->take($size);
        }

        $this->pages = (int) ceil($this->count / (double) $this->size);
    }

    public static function empty(): self
    {
        return new self([], 0, 0);
    }

    public function getFrom(): int { return $this->from; }
    public function getIndex(): int { return $this->index; }
    public function getSize(): int { return $this->size; }
    public function getCount(): int { return $this->count; }
    public function getPages(): int { return $this->pages; }
    public function getItems(): array { return $this->items->all(); }
    public function hasPrevious(): bool { return $this->index - $this->from > 0; }
    public function hasNext(): bool { return $this->index - $this->from + 1 < $this->pages; }

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'index' => $this->index,
            'size' => $this->size,
            'count' => $this->count,
            'pages' => $this->pages,
            'hasPrevious' => $this->hasPrevious(),
            'hasNext' => $this->hasNext(),
            'items' => $this->items->toArray(),
        ];
    }

    public static function from(IPaginate $source, callable $converter): self
    {
        $items = $converter($source->getItems());
        $convertedItems = collect($items);

        $instance = new self([], $source->getIndex(), $source->getSize(), $source->getFrom());
        $instance->items = $convertedItems;
        $instance->count = $convertedItems->count();
        $instance->pages = (int) ceil($instance->count / (double) $instance->size);

        return $instance;
    }
}
