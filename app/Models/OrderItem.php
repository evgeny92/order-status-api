<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_name',
        'price',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($this->price, 2, '.', ''),
        );
    }
}
