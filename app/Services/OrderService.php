<?php

namespace App\Services;

use App\Account;
use App\Currency;
use App\Models\Orders\OrderDiscount;
use App\Order;
use App\OrderDetail;
use App\OrderTransactions;
use App\Repository\Checkout\FormDetail;
use App\Services\Checkout\CheckoutOrderService;
use App\Services\Checkout\PaymentService;
use App\Traits\AuthAccountTrait;
use App\Traits\SegmentTrait;
use App\UsersProduct;
use App\VariantDetails;

class OrderService
{
	use AuthAccountTrait, SegmentTrait;

	protected $accountId, $products, $isOrderPaid, $orderRef, $formDetail, $originalFormDetail, $notes;
	protected $discount, $shipping, $tax, $subTotal;
	public $grandTotal, $isEdit, $savedOrder;

	public function __construct($data)
	{
		$this->accountId = $this->getCurrentAccountId();
		$this->orderRef = $data->orderRef;
		$this->products = $data->products;
		$this->isOrderPaid = $data->isPaid;
		$this->originalFormDetail = $data->formDetail;
		$this->formDetail = new FormDetail($data->formDetail);
		$this->notes = $data->notes;
		$this->isEdit = $data->isEdit;
		$this->discount = $data->discount;
		$this->shipping = $data->shipping;
		$this->tax = $data->tax;
		$this->savedOrder = Order::firstWhere('reference_key', $this->orderRef);
		if ($this->isEdit) {
			$this->updateOrderDiscountValue($this->savedOrder);
		}

		if (isset($this->savedOrder)) $this->savedOrder->refresh();

		$this->subTotal = $this->calculateSubTotal();
		$this->grandTotal = $this->calculateGrandTotal();
	}

	public function updateOrCreateProcessedContact()
	{
		$formDetail = new FormDetail($this->originalFormDetail);
		$formDetail->customerInfo->fullName = null;
		$processedContact = (new ProcessedContactService)->updateOrCreateProcessedContact(
			$formDetail,
			[
				'ordersCount' => $this->isEdit ? 0 : 1,
				'totalSpent' => $this->grandTotal - ($this->isEdit ? $this->savedOrder->total : 0),
				'acquisition_channel' => 'Manual'
			],
		);
		$processedContact->segmentIds = json_encode($this->getSegmentIdsByContact($processedContact));
		return $processedContact;
	}

	public function updateOrCreateOrder($processedContact)
	{
		$hasPhysicalProduct = $this->hasUnfulfilledPhysicalProduct();
		$refKeyService = new RefKeyService();

		$addressDetail = [
			'shipping_name' => $this->formDetail->shipping->fullName,
			'shipping_company_name' => $this->formDetail->shipping->companyName,
			'shipping_phoneNumber' => $this->formDetail->shipping->phoneNumber,
			'shipping_address' => $this->formDetail->shipping->address,
			'shipping_city' => $this->formDetail->shipping->city,
			'shipping_state' => $this->formDetail->shipping->state,
			'shipping_zipcode' => $this->formDetail->shipping->zipCode,
			'shipping_country' => $this->formDetail->shipping->country,

			'billing_name' => $this->formDetail->billing->fullName,
			'billing_company_name' => $this->formDetail->billing->companyName,
			'billing_address' => $this->formDetail->billing->address,
			'billing_city' => $this->formDetail->billing->city,
			'billing_state' => $this->formDetail->billing->state,
			'billing_zipcode' => $this->formDetail->billing->zipCode,
			'billing_country' => $this->formDetail->billing->country,
		];

		if ($this->isEdit) {
			$this->savedOrder->update([
				...$addressDetail,
				'fulfillment_status' => $hasPhysicalProduct ?  CheckoutOrderService::ORDER_UNFULFILLED_STATUS : CheckoutOrderService::ORDER_FULFILLED_STATUS,
				'fulfilled_at' => $hasPhysicalProduct ? null : date('Y-m-d H:i:s'),
				'additional_status' => $hasPhysicalProduct ? 'Open' : 'Closed',
				'payment_status' => PaymentService::PAYMENT_UNPAID_STATUS,
				'paided_by_customer' => $this->savedOrder->paided_by_customer,
				'taxes' => $this->calculateTax(),
				'subtotal' => $this->subTotal,
				'total' => $this->grandTotal,
				'notes' => $this->notes,
			]);
			return $this->savedOrder;
		}
		return Order::create([
			...$addressDetail,

			'is_product_include_tax' => 0,
			'tax_name' => $this->tax['name'] ?? null,
			'tax_rate' => $this->tax['rate'] ?? 0,
			'taxes' => $this->calculateTax(),

			'shipping_method_name' => $this->shipping['name'] ?? null,
			'shipping' => $this->shipping['rate'] ?? 0,

			'payment_status' => PaymentService::PAYMENT_UNPAID_STATUS,
			'payment_process_status' => PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS,
			'payment_references' => $refKeyService->getRefKey(new Order, 'payment_references'),
			'payment_method' => 'Manual Payment',
			'paided_by_customer' => 0,

			'currency' => Account::find($this->accountId)->currency,
			'exchange_rate' => Currency::firstWhere('account_id', $this->accountId)->exchangeRate,

			'subtotal' => $this->subTotal,
			'total' => $this->grandTotal,

			'fulfillment_status' => $hasPhysicalProduct ?  CheckoutOrderService::ORDER_UNFULFILLED_STATUS : CheckoutOrderService::ORDER_FULFILLED_STATUS,
			'fulfilled_at' => $hasPhysicalProduct ? null : date('Y-m-d H:i:s'),
			'additional_status' => $hasPhysicalProduct ? 'Open' : 'Closed',

			'account_id' => $this->accountId,
			'acquisition_channel' => 'Manual',
			'processed_contact_id' => $processedContact->id,
			'reference_key' => $refKeyService->getRefKey(new Order, 'order_number'),
			'order_number' => $this->getLastestOrderNumber(),
			'payment_status' => PaymentService::PAYMENT_UNPAID_STATUS,
			'notes' => $this->notes,
			'segmentIds' =>  $processedContact->segmentIds,
		]);
	}

	public function updateOrCreateOrderDetail(Order $order)
	{
		$unfulfillStatus = CheckoutOrderService::ORDER_UNFULFILLED_STATUS;
		$orderDetails = [];
		if ($this->isEdit) {
			$maxIndicator = $order->maximum_indicator;
			$selectedProduct = collect($this->products);

			$unfulfillOrder = $order->orderDetails->where('fulfillment_status', $unfulfillStatus);

			// Remove product from order details
			$unfulfillOrder->each(function ($order) use ($selectedProduct) {
				$hasVariant = $order->variant !== '[]';
				$productList = $selectedProduct->pluck('users_product_id')->toArray();
				$combinationList = $selectedProduct->pluck('variant_combination_id')->toArray();
				$isProductExist = $hasVariant
					? in_array($order->variant_combination_id, $combinationList)
					: in_array($order->users_product_id, $productList);
				if (!$isProductExist) $order->delete();
			});

			foreach ($this->products as $product) {
				$qtyDifferent = $product['quantity'] - $product['originalQuantity'];

				// If product quantity not changed
				if ($qtyDifferent === 0) continue;

				$orderDetail = $order->orderDetails()->find($product['orderDetailIds']);

				// If quantity of product added
				if ($qtyDifferent > 0) {
					$unfulfillOrderDetail = $orderDetail->firstWhere('fulfillment_status', CheckoutOrderService::ORDER_UNFULFILLED_STATUS);

					// Add quantity to order detail if unfulfill
					if (isset($unfulfillOrderDetail)) {
						$unfulfillOrderDetail->quantity += $qtyDifferent;
						$unfulfillOrderDetail->weight = $unfulfillOrderDetail->weight / $product['originalQuantity'] * $product['quantity'];
						$unfulfillOrderDetail->total = $unfulfillOrderDetail->total / $product['originalQuantity'] * $product['quantity'];
						$unfulfillOrderDetail->save();		
						continue;
					}
					// Create new order detail if all order detail fulfilled
					$product['order_id'] = $order->id;
					$product['order_number'] = "#" . $order->order_number . "-F" . $maxIndicator;
					$product['payment_status'] = $this->isOrderPaid ? PaymentService::PAYMENT_PAID_STATUS : PaymentService::PAYMENT_UNPAID_STATUS;
					$product['tracking_courier_service'] = null;
					$product['tracking_number'] = null;
					$product['tracking_url'] = null;
					$product['fulfillment_status'] = $product['fulfillmentStatus'];
					OrderDetail::create([
						'order_id' => $order->id,
						...$product,
						'quantity' => $qtyDifferent,
						'weight' => $product['weight'] * $qtyDifferent,
						'total' => $product['unit_price'] * $qtyDifferent
					]);
					continue;
				}

				// If quantity of product deducted
				$leftOverQuantity = abs($qtyDifferent);
				$orderDetail->each(function ($item) use (&$leftOverQuantity, $product) {
					if ($leftOverQuantity === 0 || $item->fulfillment_status === CheckoutOrderService::ORDER_FULFILLED_STATUS) return;

					if ($leftOverQuantity >= $item->quantity) {
						$leftOverQuantity -= $item->quantity;
						$item->delete();
					} else {
						$latestQty = $product['quantity'] - $product['min'];
						$item->weight = $item->weight / $item->quantity * $latestQty;
						$item->total = $item->total / $item->quantity * $latestQty;
						$item->quantity -= $leftOverQuantity;
						$item->save();
						$leftOverQuantity = 0;
					}
				});
			}
			return;
		}
		foreach ($this->products as $product) {
			$product['order_id'] = $order->id;
			$product['order_number'] = "#" . $order->order_number . "-F1";
			$product['payment_status'] = $this->isOrderPaid ? PaymentService::PAYMENT_PAID_STATUS : PaymentService::PAYMENT_UNPAID_STATUS;
			OrderDetail::create([
				'order_id' => $order->id,
				...$product,
				'weight' => $product['weight'] * $product['quantity'],
				'total' => $product['unit_price'] * $product['quantity']
			]);
			$orderDetails[] = $product;
		}
		return $orderDetails;
	}

	public function updateOrCreateOrderDiscount(Order $order)
	{
		if ($this->isEdit || count($this->discount) === 0) return;

		OrderDiscount::updateOrCreate(
			[
				'order_id' => $order->id
			],
			[
				'discount_code' => null,
				'discount_value' => $this->calculateDiscount() * 100,
				'display_name' => $this->discount['reason'],
				'promotion_name' => $this->discount['reason'],
				'promotion_method' => 'manual',
				'promotion_category' => 'Order',
				'discount_type' =>  $this->discount['type'],
				'discount_capped_at' => $this->discount['cappedAt'],
			]
		);
	}

	public function updateOrderDiscountValue(Order $order)
	{
		$order->orderDiscount()->each(function ($discount) use ($order) {
			$subTotal = $this->calculateSubTotal() * 100;
			$originalSubtotal = $order->subtotal * 100;
			if ($discount->promotion_category === 'Shipping') return;

			if ($discount->discount_type === 'fixed') {
				$discount->discount_value = ($discount->discount_value > $subTotal)
					? $subTotal
					: $discount->discount_value;
				$discount->save();
				return;
			}

			$cappedAt = $discount->discount_capped_at;
			$discountPrice = $discount->discount_value / ($originalSubtotal / $subTotal);
			$isExceed = is_numeric($cappedAt) && $discountPrice > ($cappedAt * 100);
			$discount->discount_value = $isExceed ? $cappedAt * 100 : $discountPrice;
			$discount->save();
		});
	}

	public function markAsPaid(Order $order)
	{
		$amountToPaid = $this->grandTotal - $order->paided_by_customer;
		$paymentStatus = PaymentService::PAYMENT_PAID_STATUS;
		if ($amountToPaid > 0 && $order->paided_by_customer > 0)
			$paymentStatus = PaymentService::PAYMENT_PARTIALLY_PAID_STATUS;

		$order->update([
			'paided_by_customer' => $this->isOrderPaid ? $this->grandTotal : $order->paided_by_customer,
			'payment_status' => $paymentStatus,
			'paid_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function updateOrCreateOrderTransaction(Order $order)
	{
		OrderTransactions::create(
			[
				'order_id' => $order->id,
				'transaction_key' => $this->getRandomId('order_transactions', 'transaction_key'),
				'total' => $order->total,
				'paid_at' => now(),
				'payment_status' => PaymentService::PAYMENT_PAID_STATUS,
			]
		);
	}

	public function calculateSubTotal()
	{
		$subTotal =  array_reduce($this->products, function ($total, $product) {
			return $total + ((float)$product['unit_price'] * (int)$product['quantity']);
		}, 0);
		return (float)number_format($subTotal, 2, '.', '');
	}

	public function calculateDiscount()
	{
		if ($this->isEdit) {
			return $this->savedOrder->orderDiscount->reduce(function ($total, $discount) {
				return $total + ((float)$discount['discount_value'] / 100);
			});
		}

		$subTotal = $this->subTotal;
		$discountPrice = 0;
		if (count($this->discount) === 0) return $discountPrice;

		$cappedAt = (float)$this->discount['cappedAt'];
		$discountValue = $this->discount['value'];
		if ($this->discount['type'] === 'percentage') {
			$discountPrice = $subTotal * ($discountValue / 100);
			$isExceed = is_numeric($this->discount['cappedAt']) && $subTotal > (float)$cappedAt;
			$discountPrice = $isExceed ? $cappedAt : $discountPrice;
		} else {
			$discountPrice = $discountValue;
		}
		return (float)$discountPrice;
	}

	public function calculateTaxableProductTotal()
	{
		return array_reduce($this->products, function ($total, $product) {
			return $total + ($product['is_taxable']
				? ((float)$product['unit_price'] * (int)$product['quantity'])
				: 0);
		}, 0);
	}

	public function calculateTax()
	{
		if ($this->isEdit) {
			$order = $this->savedOrder;

			$productTotal = $order->is_product_include_tax ? 0 : $this->calculateTaxableProductTotal();
			$shippingTotal = $order->is_shipping_fee_taxable ? $order->shipping : 0;
			$total = (float)$productTotal + (float)$shippingTotal;

			$taxRate = (float)$order->tax_rate / 100;
			$afterTax = $total * $taxRate;
			return $order->is_product_include_tax
				? $afterTax / (1 + $taxRate)
				: $afterTax;
		}

		if (count($this->tax) === 0) return 0;
		return $this->calculateTaxableProductTotal() * ((float)$this->tax['rate'] / 100);
	}

	public function calculateGrandTotal()
	{
		$discount = $this->calculateDiscount();
		$shipping = (float)($this->isEdit
			? $this->savedOrder->shipping
			: ($this->shipping['rate'] ?? 0));
		$tax = $this->calculateTax();
		return $this->subTotal - $discount + $shipping + $tax;
	}

	public function updateProductInventory(Order $order)
	{
		OrderDetail::where('order_id', $order->id)->each(function ($orderDetail) {
			$isVariant = $orderDetail->variant !== '[]';
			$inventory = $isVariant
				? VariantDetails::where('reference_key', $orderDetail['variant_combination_id'])
				: UsersProduct::where('id', $orderDetail->users_product_id);
			$remainingStock = $inventory->first()->quantity;

			if ($remainingStock !== 0) $inventory->update(['quantity' => $remainingStock - $orderDetail->quantity]);
		});
	}

	private function hasUnfulfilledPhysicalProduct()
	{
		return collect($this->products)->contains(function ($product) {
			return !$product['is_virtual'] && $product['fulfillmentStatus'] === CheckoutOrderService::ORDER_UNFULFILLED_STATUS;
		});
	}


	private function getLastestOrderNumber()
	{
		$latestOrderNumber = Order::where([
			'account_id' => $this->accountId,
			'payment_process_status' => PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS
		])->max('order_number');
		return $latestOrderNumber === null ? 1000 : $latestOrderNumber + 1;
	}
}
