<?php

namespace App\Services\Checkout;

use App\Services\RedisService;

class CheckoutErrorService
{
	public const REDIS_PROPERTY = 'checkoutError';

	public const PREFERENCES_UPDATED = 'Seller have updated some information. Please check your details again.';
	public const CHECKOUT_LIMIT_NOT_REACHED = 'Sorry, you cannot checkout with a total price lower than 2';
	public const FORM_DETAIL_EMPTY = 'Please fill in the required information';
	public const SHIPPING_METHOD_NOT_SELECTED = 'Please selected your preferred shipping method';
	public const PAYMENT_METHOD_NOT_SELECTED = 'Please selected your preferred payment method';

	public static function setError($message)
	{
		RedisService::set(self::REDIS_PROPERTY, $message);
	}

	public static function getError($isTemp = true)
	{
		$error = RedisService::get(self::REDIS_PROPERTY);
		if ($isTemp) self::deleteError();
		return $error;
	}

	public static function deleteError()
	{
		RedisService::delete(self::REDIS_PROPERTY);
	}
}
