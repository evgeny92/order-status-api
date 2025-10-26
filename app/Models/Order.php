<?php

namespace App\Models;

use App\Enum\Order\OrderStatus;
use App\Traits\OrderTags;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;

class Order extends Model
{
    use SoftDeletes, OrderTags;

    protected $fillable = [
        'order_number',
        'status',
        'total_amount'
    ];

    protected $casts = [
        'status' => OrderStatus::class
    ];

    public function tags(): belongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withPivot('added_at')
            ->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function totalAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($this->total_amount, 2, '.', ''),
        );
    }

}
