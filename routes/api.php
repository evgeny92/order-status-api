<?php

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->controller(OrderController::class)->group(function () {
    Route::get('orders', 'index');
    Route::post('orders', 'store');
    Route::get('orders/{order_number}', 'show');
    Route::post('orders/status', 'updateOrderStatus');
    //For Test
    Route::get('orders/{order_number}/external', 'updateOrderStatusFromExternal');
});
