<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTag extends Model
{
    protected $fillable = [
        'order_id', 'tag_id'
    ];
}
