<?php

namespace App\Services\Checkout\OrderFulfillment;

use App\Location;
use App\LalamoveFulfillment;
use App\LalamoveQuotation;

use App\Repository\Checkout\CheckoutData;

use App\Services\LalamoveAPIService;

class LalamoveFulfillmentService implements IOrderFulfillment
{
	protected $lalamoveAPIService;

	public function __construct()
	{
		$this->lalamoveAPIService = new LalamoveAPIService();
	}
	public function create($order, $orderDetails)
	{
		$lalamoveQuotation = CheckoutData::$shippingMethodDetail;
		$senderLocation = Location::first();

		if (!$senderLocation) {
			throw new \RuntimeException(
				'Sender location not set, cannot use Lalamove',
				'order_id: ' . $order->id,
			);
		}

		if (!isset($lalamoveQuotation)) {
			throw new \RuntimeException(
				'Failed to get lalamove quotation',
				'order_id: ' . $order->id,
			);
		}

        $lalamoveQuotation = LalamoveQuotation::create([
			'order_id' => $order->id,
			'scheduled_at' => null,
			'service_type' => $lalamoveQuotation->serviceType,
			'stops' => $this->lalamoveAPIService->makeStops(
				$senderLocation->displayAddr,
				$this->lalamoveAPIService->generateDisplayAddress([
					'street' => $order->shipping_address,
                    'city' => $order->shipping_city,
					'zip' => $order->shipping_zipcode,
					'state' => $order->shipping_state,
					'country' => $order->shipping_country,
                ])
			),
			'deliveries' => $this->lalamoveAPIService->makeDeliveries(
				$order->shipping_name,
				$order->shipping_phoneNumber,
			),
			'requester_contacts' => $this->lalamoveAPIService->makeRequesterContact($senderLocation),
			'special_requests' => [],
			'total_fee_amount' => $lalamoveQuotation->totalFee, // IMPORTANT: DON'T USE 'convertCharge' here
			'total_fee_currency' => $lalamoveQuotation->totalFeeCurrency,
		]);

		foreach ($orderDetails as $orderDetail) {
			LalamoveFulfillment::create([
				'lalamove_quotation_id' => $lalamoveQuotation->id,
				'order_detail_id' => $orderDetail->id
			]);
		}
	}
	public function fulfill()
	{
	}
}
