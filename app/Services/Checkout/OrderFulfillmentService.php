<?php

namespace App\Services\Checkout;

use App\Repository\Checkout\CheckoutData;
use App\Services\Checkout\ShippingService;
use App\Services\Checkout\OrderFulfillment\DelyvaFulfillmentService;
use App\Services\Checkout\OrderFulfillment\EasyparcelFulfillmentService;
use App\Services\Checkout\OrderFulfillment\LalamoveFulfillmentService;

class OrderFulfillmentService
{
	public static function fulfillService()
	{
		new CheckoutData();
		$shippingApp = optional(CheckoutData::$shippingMethod)->shipping_method;
		if ($shippingApp === ShippingService::EASYPARCEL_APP)
			return new EasyparcelFulfillmentService();
		if ($shippingApp === ShippingService::LALAMOVE_APP)
			return new LalamoveFulfillmentService();
		if ($shippingApp === ShippingService::DELYVA_APP)
			return new DelyvaFulfillmentService();
	}
}
