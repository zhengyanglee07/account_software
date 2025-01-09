<?php

namespace App\Services\Checkout\OrderFulfillment;

use App\EasyParcelFulfillment;
use App\EasyParcelShipment;

use App\Repository\Checkout\CheckoutData;

class EasyparcelFulfillmentService implements IOrderFulfillment
{
	public function create($order, $orderDetails)
	{
		$easyParcelShipment = EasyParcelShipment::create(array_merge(
			['order_id' => $order->id],
			(array)CheckoutData::$shippingMethodDetail,
		));

		foreach ($orderDetails as $orderDetail) {
			EasyParcelFulfillment::create([
				'easy_parcel_shipment_id' => $easyParcelShipment->id,
				'order_detail_id' => $orderDetail->id
			]);
		}
	}
	public function fulfill()
	{
	}
}
