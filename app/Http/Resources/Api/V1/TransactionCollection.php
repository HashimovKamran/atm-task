<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\Pagination\IPaginate;

class TransactionCollection extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TransactionResource::class; // Hansı resource-u istifadə edəcəyini bildirir

    /**
     * Qurucu (Constructor)
     * Paginate (və ya IPaginate implement edən) obyektini qəbul edir.
     *
     * @param IPaginate $resource
     */
    public function __construct(IPaginate $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'index' => $this->resource->getIndex(),
            'size' => $this->resource->getSize(),
            'count' => $this->resource->getCount(),
            'pages' => $this->resource->getPages(),
            'from' => $this->resource->getFrom(),
            'hasPrevious' => $this->resource->hasPrevious(),
            'hasNext' => $this->resource->hasNext(),
            'items' => TransactionResource::collection($this->resource->items),
        ];
    }
}
