<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\Delyva;
use App\DelyvaQuotation;
use App\DelyvaFulfillment;
use App\DelyvaDeliveryOrder;
use App\DelyvaDeliveryOrderDetail;
use APP\Order;
use App\OrderDetail;
use App\ProcessedContact;
use App\Location;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Services\Checkout\ShippingService;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DelyvaController extends Controller
{
    use AuthAccountTrait;

    public function getDelyvaApiInfo()
    {
        $delyvaInfo = Delyva::firstwhere([
            'account_id' => $this->getCurrentAccountId(),
        ]);
        $account = Account::firstwhere([
            'id' => $this->getCurrentAccountId(),
        ]);
        $domainDict = [
            'local' => 'https://f4f5-42-190-111-92.ap.ngrok.io', // change to domain provided by ngrok.io
            'staging' => 'https://staging.hypershapes.com',
            'production' => 'https://my.hypershapes.com',
        ];
        $domain = $domainDict[app()->environment()];
        return Inertia::render('app/pages/DelyvaPage',compact(['delyvaInfo', 'account','domain']));
    }

    public function editDelyvaApiInfo(Request $request) 
    {
        $delyvaInfo = Delyva::firstwhere([
            'account_id' => $this->getCurrentAccountId(),
         ]);

         if($delyvaInfo!== null){
             $delyvaInfo->update([
                 'delyva_company_code' => $request->companyCode,
                 'delyva_company_id' => $request->companyID,
                 'delyva_user_id' => $request->userID,
                 'delyva_customer_id' => $request->customerID,
                 'delyva_api' => $request->apiKey,
                 'item_type' => $request->itemType,
                 'delyva_selected' => true,
             ]);
         } else{
             $delyvaInfo = Delyva::create([
                 'account_id' => $this->getCurrentAccountId(),
                 'delyva_company_code' => $request->companyCode,
                 'delyva_company_id' => $request->companyID,
                 'delyva_user_id' => $request->userID,
                 'delyva_customer_id' => $request->customerID,
                 'delyva_api' => $request->apiKey,
                 'item_type' => $request->itemType,
                 'delyva_selected' => true,
               ]);
         }
        return response()->json($delyvaInfo);
    }

    public function quotationCheck(Request $request)
    {
        $accountId = $this->getCurrentAccountId();
        // $formDetail =$request->formDetail;
        $shippingService = new ShippingService();
        $params = $shippingService->getShippingParams(ShippingService::DELYVA_APP);

        $fields = $request->fields ?? $params->fields;
        $weight = $request->totalWeight ?? $params->totalWeight;

        $delyvaInfo = Delyva::firstwhere([
            'account_id' => $this->getCurrentAccountId(),
        ]);
        $sellerLocation = Location::ignoreAccountIdScope()
            ->firstwhere([
                'account_id' => $this->getCurrentAccountId(),
            ]);
        $orderId = $request->orderId;
        $orderDetail = OrderDetail::where('order_id',$orderId);
        $totalWeight = $request->selectedTotalWeight??$weight;
        if ($totalWeight <= 0)
            abort(500, 'Total weight must be greater than or equal to 0.1');
        try{
            $response = Http::baseUrl('https://api.delyva.app/v1.0/')
            ->withHeaders([
                'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api ,
            ])
            ->post('/service/instantQuote', [
                "itemType"=>strtoupper($delyvaInfo->item_type),
                "origin" => [
                    "address1"=> $sellerLocation->address1,
                    "address2"=> $sellerLocation->address2?? "",
                    "city"=> $sellerLocation->city,
                    "state"=> $sellerLocation->state,
                    "postcode"=> $sellerLocation->zip,
                    "country"=> "MY"
                ],
                "destination" => [
                    "address1"=> $fields['street'],
                    // "address2"=> "",
                    "city"=> $fields['city'],
                    "state"=> $fields['state'],
                    "postcode"=> $fields['zip'],
                    "country"=> "MY"
                ],
                "weight" =>[
                    "unit"=> "kg",
                    "value"=> $totalWeight
                ],
            ]);
        }catch(\Throwable $e){
             // generic unknown error
             \Log::error('Unknown error in getting Delyva quotation: ', [
                            'msg' => $e->getMessage()
                        ]);
            abort(500, 'Unexpected error: ' . $e->getMessage());
        }

        return $response->json();
    }

    private function fulfillOrderDetails(array $unfulfilledOrderDetails): array
    {
        $fulfilledOrderDetailIds = [];

            foreach ($unfulfilledOrderDetails as $unfulfilledOD) {
    
                $orderDetail = OrderDetail::find($unfulfilledOD['id']);
                $order = Order::find($orderDetail->order_id);
                // $order = $orderDetail->order_id;
                $order_number = $order->order_number;
    
                // full fulfillment
                if ($unfulfilledOD['quantity'] === $unfulfilledOD['max']) {

                    $orderDetail->order_number = "#" . $order_number . "-F" . $order->maximum_indicator;
                    $orderDetail->fulfillment_status = "Fulfilled";
                    $orderDetail->fulfilled_at = now();
                    $orderDetail->save();

                    $fulfilledOrderDetailIds[] = $orderDetail->id;
                }
    
                // partial fulfillment
                if ($unfulfilledOD['quantity'] < $unfulfilledOD['max']
                    && $unfulfilledOD['quantity'] > 0
                ) {
                    $totalOri = $unfulfilledOD['unit_price'] * $unfulfilledOD['quantity'];
                    $unitWeight = $unfulfilledOD['weight'] / $unfulfilledOD['max'];
                    $difference = $unfulfilledOD['max'] - $unfulfilledOD['quantity'];
                    $total = $unfulfilledOD['unit_price'] * $difference;
    
                    $orderDetail->fulfillment_status = "Unfulfilled";
                    $orderDetail->quantity = $difference;
                    $orderDetail->weight = $unitWeight * $difference;
                    $orderDetail->order_number = "";
                    $orderDetail->total = $total;
                    $orderDetail->save();
    
                    $newOrderDetail = $orderDetail->replicate();
                    $newOrderDetail->parent_id = $orderDetail->id;
                    $newOrderDetail->order_number = "#" . $order_number . "-F" . $order->maximum_indicator;
                    $newOrderDetail->fulfilled_at = now();
                    $newOrderDetail->fulfillment_status = "Fulfilled";
                    $newOrderDetail->quantity = $unfulfilledOD['quantity'];
                    $newOrderDetail->weight = $unitWeight * $unfulfilledOD['quantity'];
                    $newOrderDetail->total = $totalOri;
                    $newOrderDetail->save();
                    $fulfilledOrderDetailIds[] = $newOrderDetail->id;
                }
            };

        return $fulfilledOrderDetailIds;
    }

    public function fulfillOrderWithDelyva($reference_key, Request $request)
    {   
        $request->validate([
            'unfulfilledOrderDetails' => 'required|array',
            'quotation' => 'required|array',
            'scheduledAt' => 'nullable',
            // 'serviceType' => 'required',
            'unsettledQuotation' => 'nullable|array',
            'notes'=>'nullable',
        ]);
        $currentAccountId = $request->accountId;
        $order = Order::with('delyvaQuotations')
            ->where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        // $orderDetail = OrderDetail::find($order->id);
        $orderDetail = OrderDetail::where('order_id',$order->id);
        $totalWeight = $request->totalWeight??$orderDetail->sum('weight');
        $totalQuantity = $request->totalQuantity;
        $sellerLocation = Location::firstwhere([
            'account_id' =>  $currentAccountId,
        ]);
        $quotation  = $request->quotation;
        $shippingAmount = $quotation['price']['amount'];
        $shippingCurrency = $quotation['price']['currency'];
        $serviceType =$quotation['service']['code'];
        $serviceName = $quotation['service']['name'];
        $senderLocation = Location::first();
        $processedContact = ProcessedContact::withTrashed()->where('account_id',$currentAccountId)->get();
        $customer = $processedContact->find($order['processed_contact_id']);
        if ($totalQuantity==0) {
            return response()->json([
                'message' => 'Please make sure at least 1 quantity is select'
            ], 409);
        }
        if (!$senderLocation) {
            return response()->json([
                'message' => 'Sender location is empty'
            ], 409);
        }
        $delyvaInfo = Delyva::firstwhere([
            'account_id' =>  $currentAccountId,
        ]);
        if (!$delyvaInfo) {
            return response()->json([
                'message' => 'Please Setup Delyva Service'
            ], 409);
        }        
        if (!$delyvaInfo->delyva_selected) {
            return response()->json([
                'message' => 'Please Enable Delyva Service'
            ], 409);
        }
        DB::beginTransaction();

        try {
            ++$order->maximum_indicator;

            $order->shipping_method_name = 'Delyva';
            $order->shipping_method = 'Delyva - ' . $serviceType;
            $order->total = $order->total - $order->shipping +  (float)$shippingAmount;
            $order->shipping = (float)$shippingAmount;
            $order->save();

            $fulfilledOrderDetailIds = $this->fulfillOrderDetails($request->unfulfilledOrderDetails);
           
            $unfulfilledOD = OrderDetail::where('order_id', $order->id)
                ->where('fulfillment_status',"Unfulfilled")
                ->get();
            if($unfulfilledOD->isEmpty()){
                $order->fulfillment_status = "Fulfilled";
                $order->additional_status = "Closed";
            } else {
                $order->fulfillment_status = "Partially Fulfilled";
                $order->additional_status = "Open";
            }

            $order->save();

            $unsettledQuotation = $request->unsettledQuotation;

            // if ($unsettledQuotation && $unsettledQuotation['id'] !== $quotation['id']) {
            //     DelyvaQuotation::find($unsettledQuotation['id'])->delete();
            // }
            $delyvaQuotation = DelyvaQuotation::firstOrCreate(
                [
                    'id' => $unsettledQuotation['id'] ?? null
                ],
                [
                    'order_id' => $order->id,
                    'scheduled_at' => $request->scheduledAt,
                    'service_name' => $serviceName,
                    'service_code' => $serviceType,
                    'type'=>$delyvaInfo->item_type,
                    'total_fee_amount' => $shippingAmount,
                    'total_fee_currency' => $shippingCurrency,
                    'service_company_name' => $quotation['service']['serviceCompany']['name']
                        ??$unsettledQuotation['service_company_name'],
                    'service_detail' => json_encode($quotation['service']),
                ]
            );
            foreach ($fulfilledOrderDetailIds as $id) {

                DelyvaFulfillment::updateOrCreate(
                    [
                        'order_detail_id' => $id
                    ],
                    [
                        'delyva_quotation_id' => $delyvaQuotation->id,
                    ]
                );
            }
        } catch (\Throwable $th) {
            abort(500, $th->getMessage());
            DB::rollBack();
        }
        // external API
        try{
        $create_delyva = Http::baseUrl('https://api.delyva.app/v1.0/')
        ->withHeaders([
                'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api,
            ])
            ->post('/order?source=hypershapes', [
                "customerId"=> $delyvaInfo->delyva_customer_id,
                "process"=> true,
                "serviceCode"=> $serviceType,
                "paymentMethodId"=> 0,
                "note"=>$request->notes??"",
                "waypoint"=> [
                    [
                        "type"=> "PICKUP",
                        "scheduledAt"=> $request->scheduledAt??$delyvaQuotation->scheduled_at,
                        "inventory"=> [
                            [
                                "name"=> "ref-{$order->payment_references}",
                                "type"=> strtoupper($delyvaInfo->item_type),
                                "price"=> [
                                    "amount"=> $order->subtotal,
                                    "currency"=> "MYR"
                                ],
                                "weight"=> [
                                    "value"=> $totalWeight,
                                    "unit"=> "kg"
                                ],
                                "quantity"=> 1,
                                "description"=> ""
                            ],

                        ],
                        "contact"=> [
                            "name"=> $sellerLocation->name,
                            "email"=> $sellerLocation->email,
                            "phone"=> $sellerLocation->phone_number,
                            "unitNo"=> "",
                            "address1"=> $sellerLocation->address1,
                            "address2"=> $sellerLocation->address2??="",
                            "city"=> $sellerLocation->city,
                            "state"=> $sellerLocation->state,
                            "postcode"=> $sellerLocation->zip,
                            "country"=> "MY"
                        ]
                    ],
                    [
                        "type"=> "DROPOFF",
                        "scheduledAt"=> $request->scheduledAt??$delyvaQuotation->scheduled_at,
                        "inventory"=> 
                        [   
                            [
                                "name"=> $order->payment_references,
                                "type"=> strtoupper($delyvaInfo->item_type) ,
                                "price"=> [
                                    "amount"=> $order->subtotal,
                                    "currency"=> "MYR"
                                ],

                                "weight"=> [
                                    "value"=> $totalWeight,
                                    "unit"=> "kg"
                                ],
                                "quantity"=> 1,
                                "description"=> ""
                            ],

                        ],
                        "contact"=> 
                        [
                                "name"=> $order->shipping_company_name??$order->shipping_name,
                                "email"=> $customer->email,
                                "phone"=> $order->shipping_phoneNumber,
                                "address1"=> $order->shipping_address,
                                "address2"=> "",
                                "city"=> $order->shipping_city,
                                "state"=> $order->shipping_state,
                                "postcode"=> $order->shipping_zipcode,
                                "country"=> "MY"
                        ]
                    ],
                ],
            ]);
            // dd($create_delyva['data']);
            $orderNumber = OrderDetail::where('id', $fulfilledOrderDetailIds[0])
                ->first();
            $delyvaOrderInfo = new DelyvaDeliveryOrder();
            $delyvaOrderInfo->order_number = $orderNumber->order_number;
            $delyvaOrderInfo->delyva_quotation_id = $delyvaQuotation->id;
            $delyvaOrderInfo->delyva_order_id = $create_delyva['data']['orderId'];
            $delyvaOrderInfo->save();
        }catch (ClientException $e) {
            $resCode = $e->getCode();
            $resBody = json_decode($e->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $message = $resBody['message'];
            DB::rollBack();
            abort($resCode, 'Unknown error');
        }
        try{
            $getDelyvaOrderInfoDetail = Http::baseUrl('https://api.delyva.app/v1.0/')
            ->withHeaders([
                'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api,
            ])
            ->get("/order/{$delyvaOrderInfo->delyva_order_id}");
            $delyvaOrderInfoDetail = DelyvaDeliveryOrderDetail::Create([
                'delyva_delivery_order_id' => $delyvaOrderInfo->id,
                'serviceCode' => $getDelyvaOrderInfoDetail['data']['serviceCode'],
                'consignmentNo' => $getDelyvaOrderInfoDetail['data']['consignmentNo'],
                'invoiceId' => $getDelyvaOrderInfoDetail['data']['invoiceId'],
                'itemType' => $getDelyvaOrderInfoDetail['data']['itemType']??'PARCEL',
                'statusCode' => $getDelyvaOrderInfoDetail['data']['statusCode'],
                'status' => $getDelyvaOrderInfoDetail['data']['status'],
                'service_company_name'=>$delyvaQuotation->service_company_name,
                'service_name'=>$delyvaQuotation->service_name,
                'schedule_at'=>$request->scheduledAt??$delyvaQuotation->scheduled_at,
                'total_fee_amount'=>$quotation['price']['amount'],        
                'total_fee_currency'=>$quotation['price']['currency'],
            ]);
        }catch(\Throwable $e){
             // generic unknown error
             \Log::error('Unknown error in storing Delyva order details: ', [
                            'msg' => $e->getMessage()
                        ]);
            abort(500, 'Unexpected error: ' . $e->getMessage());
        }
        DB::commit();
        return response()->json([
            'order_id' => $order->reference_key
        ]);
    }

    public function orderTracking(Request $request)
    {
        $delyvaInfo = Delyva::firstwhere([
            'account_id' => $request->accountId,
        ]);
        $delyvaOrder = DelyvaDeliveryOrder::where([
            'delyva_order_id' =>  $request->orderId,
        ])->first();
        
        if ($request->consignmentNo===NULL){
            try{
                $delyvaOrderInfo = Http::baseUrl('https://api.delyva.app/v1.0/')
                ->withHeaders([
                    'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api,
                ])
                ->get("/order/{$request->orderId}");
                            
                $delyvaOrderInfoDetail = DelyvaDeliveryOrderDetail::where('delyva_delivery_order_id',$delyvaOrder['id'])
                ->update(['consignmentNo' => $delyvaOrderInfo['data']['consignmentNo']]);  
            }catch(\Throwable $e){
                 // generic unknown error
                 \Log::error('Unknown error in getting Delyva quotation: ', [
                                'msg' => $e->getMessage()
                            ]);
                abort(500, 'Unexpected error: ' . $e->getMessage());
            }
        }
        $delyvaOrderDetail = DelyvaDeliveryOrderDetail::where([
            'delyva_delivery_order_id' =>  $delyvaOrder->id,
        ])->first();
        if ($delyvaOrderDetail->consignmentNo){//get consignmentNo to track order
            $environment = app()->environment();
            ($environment==="local")?
                $url="https://demo.delyva.app/customer/strack?trackingNo={$delyvaOrderDetail['consignmentNo']}":
                $url="https://my.delyva.app/customer/strack?trackingNo={$delyvaOrderDetail['consignmentNo']}";
            return $url;
        }
        abort(409, 'Something went wrong on Delyva tracking, please refresh and try again later');
    }

    public function getDelyvaSettings (Request $request)
    {
        return response()->json([
            'delyva' => Delyva::where('account_id', $this->getCurrentAccountId())->first()
        ]);
    }
    public function orderUpdate (Request $request) :void
    {   
        $delyvaOrder = DelyvaDeliveryOrder
        ::where('delyva_order_id', $request->id)
        ->first();
        if($delyvaOrder){
            $delyvaOrderDetail = DelyvaDeliveryOrderDetail
                ::where('delyva_delivery_order_id', $delyvaOrder->id)
                ->first();
            $delyvaOrderDetail->consignmentNo=$request->consignmentNo;
            $delyvaOrderDetail->invoiceId=$request->invoiceId;
            $delyvaOrderDetail->failed_reason=$request->failedReason;
            $delyvaOrderDetail->statusCode=$request->statusCode;
            $delyvaOrderDetail->status=$request->status;
            $delyvaOrderDetail->schedule_at=$request->waypoint[0]['actualScheduledAt']??$request->waypoint[0]['scheduledAt'];
            $delyvaOrderDetail->company_id=$request->companyId;
            $delyvaOrderDetail->save();
        }
        // return response()->json([
        //     'delyva' => $request->companyId
        // ]);
    }
}
