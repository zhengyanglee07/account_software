<?php

namespace App\Services;

use App\Account;
use App\EasyParcelShipment;
use App\Location;
use App\OrderDetail;
use App\Libraries\EasyParcelCountryState;

class EasyParcelAPIService
{
    protected $country, $domain;

    public function getEasyParcelDomain()
    {
        // using live in staging also instead of just production, to ease the testing process
        $isLocal = app()->environment('local');
        $domain = [
            'malaysia' => [
                'demo' => 'https://demo.connect.easyparcel.my/?ac=',
                'live' => 'https://connect.easyparcel.my/?ac=',
            ],
            'singapore' => [
                'demo' => 'https://demo.connect.easyparcel.sg/?ac=',
                'live' => 'https://connect.easyparcel.sg/?ac=',
            ],
            'thailand' => [
                'demo' => 'http://demo.connect.easyparcel.co.th/?ac=',
                'live' => 'http://connect.easyparcel.co.th/?ac=',
            ],
            'indonesia' => [
                'demo' => 'http://demo.connect.easyparcel.co.id/?ac=',
                'live' => 'http://connect.easyparcel.co.id/?ac=',
            ],
        ];
        return $domain[strtolower($this->country)][$isLocal ? 'demo' : 'live'];
    }

    /**
     * Rate checking by using EasyParcel API.
     *
     * Ref: https://developers.easyparcel.com/?pg=DocAPI&c=Malaysia&type=Individual#nav_Individual_EPRateCheckingBulk
     *
     * @param $accountId
     * @param array|null $fields Extra fields for rate checking query, e.g. weight, send_code, etc in accordance to ref url above
     * @return array
     */
    public function rateChecking($accountId, ?array $fields = []): array
    {
        $easyParcel = $this->getEasyParcel($accountId);
        $location = $this->getSenderLocation($accountId);

        // EasyParcel in hypershapes support [Malaysia, Singapore,Thailand, Indonesia], currently (Feb 2022)
        $supportedCountries = ['malaysia', 'singapore', 'thailand', 'indonesia'];
        if (!$easyParcel || !$location || !in_array(strtolower($location->country), $supportedCountries)) {
            return [];
        }

        $senderLocation = $this->getCountryStateCode($location->country, $location->state);
        $receiverLocation = $this->getCountryStateCode($fields['send_country'], $fields['send_state']);

        $postparam = [
            'api' => $easyParcel->api_key,
            'bulk' => [
                [
                    'pick_code' => $location->zip,
                    'pick_state' => $senderLocation['state'],
                    'pick_country' => $senderLocation['country'],
                    'send_code' => $fields['send_code'] ?? '',
                    'send_state' => $receiverLocation['state'],
                    'send_country' =>  $receiverLocation['country'],
                    'weight' => $fields['weight'] ?? '',
                    'width' => '0',
                    'length' => '0',
                    'height' => '0',
                    'date_coll' => $fields['date_coll'] ?? null,
                ]
            ],
            'exclude_fields' => [
                'pgeon_point',
            ]
        ];

        return $this->makeCurlPostRequest("EPRateCheckingBulk", $postparam);
    }

    /**
     * @param EasyParcelShipment $easyParcelShipment
     * @param array $fulfilledOrderDetailIds
     * @param array|null $fields
     * @return array
     */
    public function makeOrder(EasyParcelShipment $easyParcelShipment, array $fulfilledOrderDetailIds, ?array $fields = []): array
    {
        $order = $easyParcelShipment->order;
        $accountId = $order->account_id;

        $easyParcel = $this->getEasyParcel($accountId);
        $location = $this->getSenderLocation($accountId);

        // EasyParcel in hypershapes support [Malaysia, Singapore,Thailand, Indonesia], currently (Feb 2022)
        $supportedCountries = ['malaysia', 'singapore', 'thailand', 'indonesia'];
        if (!$easyParcel || !$location || !in_array(strtolower($location->country), $supportedCountries)) {
            return [];
        }

        // this should be able to handle cases for fulfilled and partially fulfilled order
        $fulfilledOrderDetails = OrderDetail
            ::whereIn('id', $fulfilledOrderDetailIds)
            ->get();
        $productTotalWeight = $fulfilledOrderDetails->reduce(function ($carry, $od) {
            $weight = (float)$od->weight ?: $od->usersProduct->weight; // users product weight is a fallback for manual & legacy order (March 2020)
            return $carry + $weight;
        }, 0.00);

        $senderLocation = $this->getCountryStateCode($location->country, $location->state);
        $receiverLocation = $this->getCountryStateCode($order->shipping_country, $order->shipping_state);

        $postparam = [
            'api' => $easyParcel->api_key,
            'bulk' => [
                [
                    'weight' => $productTotalWeight,
                    'width' => '0',
                    'length' => '0',
                    'height' => '0',
                    'content' => $order->orderDetails[0]->usersProduct->productTitle, // sampling parcel content from order detail
                    'value' => $order->orderDetails->count(),
                    'service_id' => $easyParcelShipment->service_id,
                    'pick_name' => $location->name,
                    'pick_company' => $location->name,
                    'pick_contact' => $location->phone_number,
                    'pick_mobile' => $location->phone_number,
                    'pick_addr1' => $location->address1,
                    'pick_addr2' => $location->address2,
                    'pick_addr3' => '',
                    'pick_addr4' => '',
                    'pick_city' => $location->city,
                    'pick_state' => $senderLocation['state'],
                    'pick_code' => $location->zip,
                    'pick_country' => $senderLocation['country'], // currently only supports MY, SG, TH, ID
                    'send_name' => $order->shipping_name,
                    'send_company' => '',
                    'send_contact' => $order->shipping_phoneNumber,
                    'send_mobile' => $order->shipping_phoneNumber,
                    'send_addr1' => $order->shipping_address,
                    'send_addr2' => '',
                    'send_addr3' => '',
                    'send_addr4' => '',
                    'send_city' => $order->shipping_city,
                    'send_state' => $receiverLocation['state'],
                    'send_code' => $order->shipping_zipcode,
                    'send_country' => $receiverLocation['country'], // currently only supports MY, SG, TH, ID
                    'send_email' => $order->processedContact->email,
//                    'collect_date' => '2020-02-20',
                    'hs_code' => '',
                    'REQ_ID' => $order->reference_key,
                    'reference' => $order->reference_key,
                ],
            ],
        ];

        return $this->makeCurlPostRequest("EPSubmitOrderBulk", $postparam);
    }

    /**
     * @param $easyParcelShipment
     * @param array|null $fields
     * @return array
     */
    public function makeOrderPayment($easyParcelShipment, ?array $fields = []): array
    {
        if (is_null($easyParcelShipment)) {
            throw new \RuntimeException('Order has no EasyParcel shipment');
        }

        $accountId = $easyParcelShipment->order->account_id;

        $easyParcel = $this->getEasyParcel($accountId);
        $location = $this->getSenderLocation($accountId);

        // EasyParcel in hypershapes support only Malaysia, currently (Feb 2020)
        if (!$easyParcel || !$location || strtolower($location->country) !== 'malaysia') {
            return [];
        }

        $postparam = [
            'api' => $easyParcel->api_key,
            'bulk' => [
                [
                    'order_no' => $easyParcelShipment->order_number,
                ],
            ],
        ];

        return $this->makeCurlPostRequest("EPPayOrderBulk", $postparam);
    }

    /**
     * @param $order
     * @return array
     */
    public function checkOrderStatus($order): array
    {
        $accountId = $order->account_id;
        
        $this->getSenderLocation($accountId);

        $easyParcel = $this->getEasyParcel($accountId);

        if (!$easyParcel) {
            return [];
        }

        $easyParcelShipments = $order->easyParcelShipments;
        if ($easyParcelShipments->count() === 0) {
            throw new \RuntimeException('Order has no EasyParcel shipment');
        }

        $postparam = [
            'api' => $easyParcel->api_key,
            'bulk' => $easyParcelShipments
                ->map(static function ($es) {
                    return ['order_no' => $es->order_number];
                })
                ->toArray()
        ];

        return $this->makeCurlPostRequest("EPOrderStatusBulk", $postparam);
    }

    private function getEasyParcel($accountId)
    {
        return optional(Account::find($accountId))->easyParcel;
    }

    private function getSenderLocation($accountId)
    {
        $location = Location::ignoreAccountIdScope()->where('account_id', $accountId)->first();
        $this->country = $location->country;
        return $location;
    }

    /**
     * General curl ops for EasyParcel API
     *
     * @param string $action
     * @param array $postparam
     * @return array
     */
    private function makeCurlPostRequest(string $action, array $postparam): array
    {
        $domain = $this->getEasyParcelDomain();
        $url = $domain . $action;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postparam));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        ob_start();
        $return = curl_exec($ch);
        ob_end_clean();
        curl_close($ch);

        return json_decode($return, true);
    }

    public function getCountryStateCode($country, $state)
    {
        $easyParcelCountryState = new EasyParcelCountryState();
        return [
            'country' =>  $easyParcelCountryState->getCountryCode($country),
            'state' =>  $easyParcelCountryState->getStateCode($country, $state),
        ];
    }
}
