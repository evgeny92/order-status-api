<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Tag\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class OrderCreatedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount_formatted,
            'status' => $this->status,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'items' => OrderItemsResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
