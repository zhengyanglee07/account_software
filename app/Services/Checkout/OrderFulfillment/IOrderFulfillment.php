<?php

namespace App\Services\Checkout\OrderFulfillment;

use App\Order;

interface IOrderFulfillment
{
	/**
	 * Create new shipment and fulfillment
	 * 
	 * This function will call when new order placed by customer 
	 * need to fulfill by (easyparcel, lalamove, delyva)
	 */
	public function create(Order $order, array $orderDetails);

	/**
	 * Fulfill order by courier
	 * 
	 * This function will call when admin decide to fulfill order
	 */
	public function fulfill();
}
