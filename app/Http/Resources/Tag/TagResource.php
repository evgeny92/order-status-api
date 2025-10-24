<?php

namespace App\Http\Resources\Tag;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'added_at' => $this->whenPivotLoaded('order_tag', function () {
                return Carbon::parse($this->pivot->added_at)->toDateTimeString();
            }),
        ];
    }
}
