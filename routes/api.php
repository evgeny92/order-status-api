<?php

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->controller(OrderController::class)->group(function () {
    Route::post('orders', 'store');

});
