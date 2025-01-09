<?php

namespace App\Jobs;

use App\LalamoveDeliveryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLalamoveOrderDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private LalamoveDeliveryOrder $lalamoveDeliveryOrder;
    private array $orderDetails;

    /**
     * Create a new job instance.
     *
     * @param LalamoveDeliveryOrder $lalamoveDeliveryOrder
     * @param array $orderDetails
     */
    public function __construct(LalamoveDeliveryOrder $lalamoveDeliveryOrder, array $orderDetails)
    {
        $this->lalamoveDeliveryOrder = $lalamoveDeliveryOrder;
        $this->orderDetails = $orderDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->lalamoveDeliveryOrder->lalamoveDeliveryOrderDetail->update([
            'driver_id' => $this->orderDetails['driverId'],
            'share_link' => $this->orderDetails['shareLink'],
            'status' => $this->orderDetails['status'],
            'price' => $this->orderDetails['price']
        ]);
    }
}
