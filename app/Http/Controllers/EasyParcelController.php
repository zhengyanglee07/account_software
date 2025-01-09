<?php

namespace App\Http\Controllers;

use App\EasyParcelShipment;
use App\Order;

use App\Services\EasyParcelAPIService;
use App\Services\Checkout\ShippingService;

use App\Traits\AuthAccountTrait;

use Illuminate\Http\Request;

class EasyParcelController extends Controller
{
    use AuthAccountTrait;

    private $easyParcelAPIService;

    public function __construct(EasyParcelAPIService $easyParcelAPIService)
    {
        $this->easyParcelAPIService = $easyParcelAPIService;
    }

    public function shippingRateChecking(Request $request)
    {
        $accountId = $this->getCurrentAccountId();

        $shippingService = new ShippingService();
        $fields = $request->fields ?? $shippingService->getShippingParams(ShippingService::EASYPARCEL_APP)->fields;

        $json = $this->easyParcelAPIService->rateChecking($accountId, $fields);
        $this->abortIfEasyParcelAPIError($json);

        // filter out dropoff & pgeon deliveries
        $methods = array_filter($json['result'][0]['rates'] ?? [], function ($m) {
            return $m['service_detail'] !== 'dropoff' // implement dropoff later
                && $m['courier_name'] !== 'Pgeon';
        });

        return response()->json([
            // using "methods" here to stay consistent with hypershapes shipping naming
            'methods' => array_values($methods)
        ]);
    }

    public function parcelOrderStatusChecking(Request $request)
    {
        $request->validate([
            'orderId' => 'required'
        ]);

        $order = Order
            ::with('easyParcelShipments')
            ->findOrFail($request->orderId);

        $json = $this->easyParcelAPIService->checkOrderStatus($order);
        $this->abortIfEasyParcelAPIError($json);

        //        $orderStatus = $json['result'][0]['order_status'] ?? null;
        $result = $json['result'];
        $statuses = [];

        if (count($result) === 0) {
            return response()->json(['orderStatuses' => $statuses]);
        }

        foreach ($result as $resultItem) {
            $easyParcelShipment = EasyParcelShipment
                ::where([
                    'order_id' => $order->id,
                    'order_number' => $resultItem['order_no']
                ])
                ->first();

            if ($easyParcelShipment) {
                $status = $resultItem['order_status'];
                $easyParcelShipment->update(['order_status' => $status]);
                $statuses[] = $status;
            }
        }

        return response()->json([
            'orderStatuses' => $statuses
        ]);
    }

    private function abortIfEasyParcelAPIError($res): void
    {
        if (isset($res['api_status']) && $res['api_status'] !== 'Success') {
            abort('409', $res['error_remark']);
        }
    }
}
