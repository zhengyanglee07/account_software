<?php

namespace App\Services\Checkout;

use App\Delyva;
use App\EasyParcel;
use App\Lalamove;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;
use App\ShippingZone;

use App\Traits\AuthAccountTrait;
use App\Traits\Checkout\CheckoutCurrencyTrait;
use Illuminate\Http\Request;

class ShippingService
{
	use AuthAccountTrait, CheckoutCurrencyTrait;

	// Shipping method name
	public const MANUAL_SHIPPING_FLAT_RATE = 'Flat Rate';
	public const MANUAL_SHIPPING_BASED_ON_WEIGHT = 'Based On Weight';
	public const EASYPARCEL_APP = 'EasyParcel';
	public const LALAMOVE_APP = 'Lalamove';
	public const DELYVA_APP = 'Delyva';

	protected $formService, $totalWeight;
	protected $address;

	public function __construct()
	{
		new CheckoutData();

		$this->formService = new FormServices();
		$this->totalWeight = CheckoutRepository::$totalWeight;
		$this->address = CheckoutData::$formDetail->shipping;
	}

	public function matchesShippingCountry($country)
	{
		return static function ($query) use ($country) {
			$query->where('country', json_encode($country));
		};
	}

    public function getManualShipping()
    {
        $shippingZone = ShippingZone::with(['shippingRegion' => $this->matchesShippingCountry($this->address->country)])
            ->whereHas('shippingRegion', $this->matchesShippingCountry($this->address->country))
            ->whereAccountId($this->getCurrentAccountId());

        $availableShipping = [];
        $shippingZone->each(function ($query) use (&$availableShipping) {
            $shippingRegion = $query->shippingRegion->first();
            $isValid = false;
            if ($shippingRegion->region_type === 'zipcodes') {
                $zipCodes = json_decode($shippingRegion->zipcodes, true);
                $isValid = in_array($this->address->zipCode, $zipCodes);
            } else {
                $states = array_filter($shippingRegion->state, function ($row) {
                    return $row['isChecked'] && $row['stateName'] === $this->address->state;
                });
                $isValid = count($states) > 0;
            }
            if (!$isValid) return;

            if (CheckoutRepository::$isFunnel) {
                $manualShipping = $query->shippingMethodDetails->firstWhere('shipping_method', ShippingService::MANUAL_SHIPPING_FLAT_RATE);
                $shippingMethod = isset($manualShipping) ? [$manualShipping] : [];
            } else
                $shippingMethod = $query->shippingMethodDetails->toArray();

            if (count($shippingMethod ?? []) > 0) $availableShipping = array_merge($availableShipping, $shippingMethod);
        });

        // Return Rest Of World Shipping if not available shipping methods
        if (count($availableShipping) === 0) {
            $shippingZone = ShippingZone::with(['shippingRegion' => $this->matchesShippingCountry('Rest of world')])
                ->whereHas('shippingRegion', $this->matchesShippingCountry('Rest of world'))
                ->whereAccountId($this->getCurrentAccountId())->first();
            if (!isset($shippingZone)) return [];

            if (CheckoutRepository::$isFunnel) {
                $manualShipping = $shippingZone->shippingMethodDetails->firstWhere('shipping_method', ShippingService::MANUAL_SHIPPING_FLAT_RATE);
                $shippingMethod = isset($manualShipping) ? [$manualShipping] : [];
            } else
                $shippingMethod = $shippingZone->shippingMethodDetails->toArray();
            if (count($shippingMethod ?? []) > 0) $availableShipping = array_merge($availableShipping, $shippingMethod);
        }
        return $availableShipping;
    }

	/**
	 * Used for set params required for specific shipping app (easyparcel, lalamove, delyva)
	 *
	 * @param  string $app
	 * @return object
	 */
	public function getShippingParams(string $app)
	{

		$address = $this->address;
		$weight = $this->totalWeight;

		$phoneNumber = preg_replace('/[^0-9]/', '', $address->phoneNumber);

		$params = [];

		if (!isset($address)) return $params;

		switch ($app) {
			case self::EASYPARCEL_APP:
				$params =
					[
						'fields' =>
						[
							'send_code' => $address->zipCode,
							'send_state' => $address->state,
							'send_country' => $address->country,
							'weight' => $weight,
						]
					];

				break;
			case self::LALAMOVE_APP:
				$params = [
					'name' => $address->fullName,
					'phone' => $phoneNumber,
					'city' => $address->city,
					'country' => $address->country,
					'state' => $address->state,
					'street' => $address->address,
					'zip' => $address->zipCode,
				];
				break;
			case self::DELYVA_APP:
				$params = [
					'fields' => [
						'name' => $address->fullName,
						'phone' => $phoneNumber,
						'city' => $address->city,
						'country' => $address->country,
						'state' => $address->state,
						'street' => $address->address,
						'zip' => $address->zipCode,
					],
					'totalWeight' => $weight,
				];
				break;

			default:
				# code...
				break;
		}

		return (object)$params;
	}

	public function getSelectedManualShipping()
	{
		$allManualShipping = $this->getManualShipping();
		$selectedShipping = array_filter($allManualShipping, function ($shipping) {
			return $shipping['id'] === (int)optional(CheckoutData::$shippingMethod)->shipping_id;
		});
		$selectedShipping = array_values($selectedShipping);
		if (!isset($selectedShipping[0])) return;
		return (object)$selectedShipping[0];
	}

	public function getSelectedShippingMethodName()
	{
		return optional(CheckoutData::$shippingMethod)->shipping_method;
	}

	public function getSelectedShippingMethodDetail()
	{
		new CheckoutData();
		$app = $this->getSelectedShippingMethodName();
		$request = new Request();

		if ($app === self::MANUAL_SHIPPING_BASED_ON_WEIGHT || $app === self::MANUAL_SHIPPING_FLAT_RATE)
			return $this->getSelectedManualShipping();

		$selectedShippingId = CheckoutData::$shippingMethod->shipping_id;
		if ($app === self::EASYPARCEL_APP) {
			$response = app('App\Http\Controllers\EasyParcelController')->shippingRateChecking($request);
			$methods = $response->getData()->methods;
			$selected = array_filter($methods, function ($method) use ($selectedShippingId) {
				return $method->service_id === $selectedShippingId;
			});
			return array_values($selected)[0] ?? null;
		}
		if ($app === self::LALAMOVE_APP) {
			$response = app('App\Http\Controllers\LalamoveController')->quotationChecking($request);
			$serviceType = str_replace('lalamove', '', $selectedShippingId);
			$data = $response->getData()->quotations->{$serviceType};
			if (!isset($data)) return;
			$data->serviceType = $serviceType;
			return $data;
		}
		if ($app === self::DELYVA_APP) {
			$response = app('App\Http\Controllers\DelyvaController')->quotationCheck($request);
			$methods = $response['data']['services'];
			$selected = array_filter($methods, function ($method) use ($selectedShippingId) {
				$serviceCode = str_replace('delyva', '', $selectedShippingId);
				return $method['service']['code'] === $serviceCode;
			});
			return array_values($selected)[0] ?? null;
		}
	}

	/**
	 * Used for calculate shipping charge of manual shipping method (based on weight / flat rate)
	 *
	 * @param  mixed $weight
	 * @return void
	 */
	public function calculateShippingCharge(int|float $weight)
	{
		$shippingMethod = CheckoutData::$shippingMethodDetail;
		$charge = 0;
		if ($shippingMethod->shipping_method === self::MANUAL_SHIPPING_BASED_ON_WEIGHT) {
			if ($weight <= (float)$shippingMethod->first_weight && $weight >= 0)
				$charge = $shippingMethod->first_weight_price;
			else if ($weight > (float)$shippingMethod->first_weight) {
				$remainWeight = $weight - (float)$shippingMethod->first_weight;
				$additionalCharge = ($remainWeight / (float)$shippingMethod->additional_weight) * $shippingMethod->additional_weight_price;
				$charge = (float)$shippingMethod->first_weight_price + ($additionalCharge ?? 0);
			}
		} else if ($shippingMethod->shipping_method === self::MANUAL_SHIPPING_FLAT_RATE)
			$charge = $shippingMethod->per_order_rate;
		return $this->getConversionPrice($charge);
	}

	/**
	 * Get shipping charge of selected shipping method
	 *
	 * ! Call this function after set shipping method detail !
	 * ! How to set: CheckoutData::setSelectedShippingMethodDetail !
	 *
	 * @param  mixed $weight
	 * @return void
	 */
	public function getShippingCharge()
	{
		$app = $this->getSelectedShippingMethodName();

		if ($app === self::MANUAL_SHIPPING_BASED_ON_WEIGHT || $app === self::MANUAL_SHIPPING_FLAT_RATE)
			return $this->calculateShippingCharge($this->totalWeight);

		$shippingDetail = CheckoutData::$shippingMethodDetail;
		if ($app === self::EASYPARCEL_APP)
			return $shippingDetail->price;
		if ($app === self::LALAMOVE_APP)
			return $shippingDetail->totalFee;
		if ($app === self::DELYVA_APP)
			return $shippingDetail['price']['amount'];
	}

	public static function hasSelectedShippingMethod()
	{
		return count((array)CheckoutData::$shippingMethod);
	}

	public function checkShippingMethodAvailability()
	{
		$address = $this->address;
		$shippingMethod = $this->getManualShipping();

		if (count($shippingMethod) > 0) return true;

		$request = new Request();
		$request->fields = [
			'send_code' => $address->zipCode,
			'send_state' => $address->state,
			'send_country' => $address->country,
			'street' => $address->address,
			'city' => $address->city,
			'state' => $address->state,
			'zip' => $address->zipCode,
			'country' => $address->country,
			'weight' => 1,
			'phone' => '01212121212',
			'name' => 'None',
			'scheduleAt' => null,
			'date_coll' => null,
		];
		$accountId = $this->getCurrentAccountId();
		$hasEasyParcel = EasyParcel::where('account_id', $accountId)->where('easyparcel_selected', 1)->exists();
		$hasLalamove = Lalamove::where('account_id', $accountId)->where('lalamove_selected', 1)->exists();
		$hasDelyva =  Delyva::where('account_id', $accountId)->where('delyva_selected', 1)->exists();
		$request->accountId = $accountId;
		$request->totalWeight = 1;

		// Currently supported countries of easy parcel
		$supportedCountries = ['Malaysia', 'Singapore', 'Thailand', 'Indonesia'];
		if ($hasEasyParcel && in_array($address->country, $supportedCountries)) {
			try {
				$easyparcel = app('App\Http\Controllers\EasyParcelController')->shippingRateChecking($request);
				$shippingMethod = [...$shippingMethod, ...$easyparcel->original['methods']];
			} catch (\Throwable $th) {
			}
		}
		if ($hasLalamove && $address->country === 'Malaysia') {
			try {
				$lalamove = app('App\Http\Controllers\LalamoveController')->quotationChecking($request);
				foreach ($lalamove->original['quotations'] as $value) {
					array_push($shippingMethod, $value);
				}
			} catch (\Throwable $th) {
			}
		}
		if ($hasDelyva && $address->country === 'Malaysia') {
			try {
				$delyva = app('App\Http\Controllers\DelyvaController')->quotationCheck($request);
				$shippingMethod = [...$shippingMethod, ...$delyva['data']['services']];
			} catch (\Throwable $th) {
			}
		}

		return count($shippingMethod) > 0;
	}
}
