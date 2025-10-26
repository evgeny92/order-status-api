<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('orders')->info('Order updated', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status'   => $this->order->status->value,
            'tags'     => $this->order->getTagNames(), //from Trait
            'updated_at' => $this->order->updated_at->toDateTimeString(),
        ]);
    }
}
