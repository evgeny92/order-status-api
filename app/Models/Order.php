<?php

namespace App\Models;

use App\Enum\Order\OrderStatus;
use App\Traits\OrderTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

}
