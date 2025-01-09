<?php

namespace App\Services\Checkout\OrderFulfillment;

use App\Delyva;
use App\DelyvaQuotation;
use App\Location;

use App\Repository\Checkout\CheckoutData;

use App\Traits\AuthAccountTrait;

class DelyvaFulfillmentService implements IOrderFulfillment
{
	use AuthAccountTrait;

	public function create($order, $orderDetails)
	{
		$delyvaQuotation = CheckoutData::$shippingMethodDetail;
		$delyvaInfo =  Delyva::firstWhere('account_id', $this->getCurrentAccountId());
		$senderLocation = Location::first();
		if (!$senderLocation) {
			throw new \RuntimeException(
				'Sender location not set, cannot use Delyva',
				'order_id: ' . $order->id
			);
		}
		$delyvaQuotation = DelyvaQuotation::create([
			'order_id' => $order->id,
			'scheduled_at' => null,
			'service_code' => $delyvaQuotation['service']['code'],
			'service_name' => $delyvaQuotation['service']['name'],
			'type' => $delyvaInfo->item_type,
			'total_fee_amount' => $delyvaQuotation['price']['amount'], // IMPORTANT: DON'T USE 'convertCharge' here
			'total_fee_currency' => $delyvaQuotation['price']['currency'],
			'service_company_name' => $delyvaQuotation['service']['serviceCompany']['name'],
			'service_detail' => json_encode($delyvaQuotation['service']),
		]);
	}
	public function fulfill()
	{
        // TODO: ZHENGYANG
	}
}
