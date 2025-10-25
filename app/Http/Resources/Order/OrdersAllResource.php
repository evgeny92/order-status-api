<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Tag\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class OrdersAllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'total_amount' => Number::format($this->total_amount, 2),
            'status' => $this->status,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
