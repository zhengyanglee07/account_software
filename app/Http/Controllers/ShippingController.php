<?php

namespace App\Http\Controllers;

use App\EasyParcel;
use App\Lalamove;
use Illuminate\Http\Request;
use Auth;
use App\ShippingMethodDetail;
use App\ShippingZone;
use App\ShippingMethod;
use Hashids\Hashids;
use App\ShippingRegion;
use App\Account;
use App\Currency;
use App\Delyva;
use App\EcommercePreferences;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Location;
use Illuminate\Support\Str;
use Inertia\Inertia;
class ShippingController extends Controller
{

    private function current_accountId()
	{
        return Auth::user()->currentAccountId;
    }


    public function showShippingSettings($type = null){
        $hashids = new Hashids('', 10);
        $account = Auth::user()->currentAccount();
        $timeZone = $account->timeZone;
        $shippingZone = ShippingZone::where('account_id',$account->id)->get();

        foreach ($shippingZone as $shipping){
            $shipping->urlId = $hashids->encode($shipping->id);
        }
        $location = Location::where('account_id',$account->id)->first();
        if(Str::contains( url()->current(), 'onboarding')){
            return Inertia::render('onboarding/pages/ShippingSettingForm', compact('type','shippingZone','timeZone','location'));
        }
        return Inertia::render('setting/pages/ShippingSettings', compact('shippingZone','timeZone','location'));
    }

    public function createShippingZones(){
        $defaultCurrency = $this->getDefaultCurrency();
        return Inertia::render('setting/pages/AddNewShipping', compact('defaultCurrency'));
    }

    public function saveNewZone(Request $request){
        $user = Auth::user();
        if($request->type=="edit"){
            $newZone = ShippingZone::where('account_id',$user->currentAccountId)->where('id',$request->shippingZone['id'])->first();
        }else{
            $newZone = new ShippingZone();
            $newZone->account_id = $user->currentAccountId;
        }
        $newZone->zone_name = $request->zoneName;
        $newZone->save();
        if(isset($request->deletedCountry)){
            foreach ($request->deletedCountry as $countryId){
                if($countryId !== ''){
                    ShippingRegion::destroy($countryId);
                }
            }
        }
        if(count($request->countryData) > 0){
            foreach($request->countryData as $data){
                if($request->type=="edit"){

                       ShippingRegion::updateOrCreate(
                         ['id'=> $data['id']],
                         ['zone_id'=>$newZone->id,
                          'country' => $data['selectedCountry'],
                          'region_type' =>$data['selectedType'],
                          'zipcodes' => json_encode($data['zipcode']),
                          'state'=> ($data['selectedType'] == 'states') ? $data['state'] : []
                         ]);

                }else{
                    $newRegion = new ShippingRegion();
                    $newRegion->zone_id = $newZone->id;
                    $newRegion->country = $data['selectedCountry'];
                    $newRegion->region_type = $data['selectedType'];
                    $newRegion->zipcodes = json_encode($data['zipcode']);
                    $newRegion->state = ($data['selectedType'] == 'states') ? $data['state'] : [];
                    $newRegion->save();
                }

            }
        }
        if ($request->shippingMethodData !== []) {
            foreach ($request->shippingMethodData as $data) {
                $newData = $data['type'] === 'bow' ?
                    [
                        'shipping_name' => $data['shippingName'],
                        'first_weight' => ($data['firstWeight']) ? ($data['firstWeight']) : 0,
                        'first_weight_price' => ($data['firstMoney']) ? $data['firstMoney'] : 0,
                        'additional_weight' => $data['additionalWeight'] ? $data['additionalWeight'] : 0,
                        'additional_weight_price' => $data['additionalMoney'] ? $data['additionalMoney'] : 0,
                        'free_shipping' => $data['isFreeshipping'],
                        'free_shipping_on' => $data['freeShipFrom']
                    ] :
                    [
                        'shipping_name' => $data['shippingName'],
                        'per_order_rate' => $data['perOrderRate']  ? $data['perOrderRate'] : 0,
                        'free_shipping' => $data['isFreeshipping'],
                        'free_shipping_on' => $data['freeShipFrom']
                    ];

                $newShippingFee = ShippingMethodDetail::updateOrCreate(
                    ['id' => $data['id']],
                    $newData
                );

                ShippingMethod::updateOrCreate(
                    ['shipping_detail_id' => $newShippingFee->id],
                    [
                        'shipping_zone_id' => $newZone->id,
                        'shipping_method' => $data['type'] === 'bow' ? 'Based On Weight' : 'Flat Rate',
                    ]
                );
            }
        }

        if (isset($request->deletedMethod)) {
            foreach ($request->deletedMethod as $methodId) {
                if ($methodId !== '') {
                    ShippingMethodDetail::destroy($methodId);
                }
            }
        }
    }

    public function editShippingSetting($urlId){

        $hashids = new Hashids('',10);
        $idArr = $hashids->decode($urlId);
        $id = $idArr[0];
        $account= $this->currentAccount();
        $shippingMethods = \DB::table('shipping_methods')
        ->join('shipping_zones','shipping_zones.id','=','shipping_methods.shipping_zone_id')
        ->join('shipping_method_details','shipping_methods.shipping_detail_id','=','shipping_method_details.id')
        ->where('account_id',$account->id)
        ->where('shipping_methods.shipping_zone_id',$id)
        ->get();
        $shippingZoneAll = $account->shippingZone;
        $shippingZone = $shippingZoneAll->where('id',$id)->first();
        $shippingRegion = $shippingZone->shippingRegion;
        $otherExistedRegion = \DB::table('shipping_regions')
        ->join('shipping_zones','shipping_regions.zone_id','=','shipping_zones.id')
        ->where('zone_id','!=',$id)->where('account_id',$account->id)->get();

        $isNew = false;
        $defaultCurrency = $this->getDefaultCurrency();
        return Inertia::render('setting/pages/AddNewShipping',compact('isNew','defaultCurrency','shippingZone','shippingMethods','shippingRegion','otherExistedRegion'));
    }

    public function fetchCountryAndRegion(){
        $account= $this->currentAccount();
        $existedRegion = \DB::table('shipping_regions')
        ->join('shipping_zones','shipping_regions.zone_id','=','shipping_zones.id')
        ->select('shipping_zones.zone_name','shipping_regions.*')
        ->where('account_id',$account->id)->get();
        $path = storage_path() . "/json/data.json";
        $API = json_decode(file_get_contents($path), true);

        return response()->json(['API'=>$API,'existedRegion'=>$existedRegion]);

    }

    public function deleteShippingZone(Request $request){
        $account= $this->currentAccount();
        $shippingZone = ShippingZone::where('account_id',$account->id)->where('id',$request->id)->first();
        $allMethodDetail = $shippingZone->shippingMethodDetails;
        foreach($allMethodDetail as $method){
            $method->delete();
        }
        $shippingZone->delete();
    }

    public function checkShippingSettings(){
        $accountId = $this->currentAccount()->id;
        $hasEasyparcel = optional(EasyParcel::where(['account_id' => $accountId, 'easyparcel_selected' => 1]))->exists();
        $hasLalamove = optional(Lalamove::where(['account_id' => $accountId, 'lalamove_selected' => 1]))->exists();
        $hasDelyva = optional(Delyva::where(['account_id' => $accountId, 'delyva_selected' => 1]))->exists();

        return response()->json([
            'hasEasyparcel' => $hasEasyparcel,
            'hasLalamove' => $hasLalamove,
            'hasDelyva' => $hasDelyva,
        ]);
    }

    public function getEasyParcelSettings()
    {
        return response()->json([
            'apiKey' => optional(EasyParcel::where('account_id', $this->currentAccount()->id)->first())->api_key
        ]);
    }

    public function storeEasyParcelSettings(Request $request)
    {
        EasyParcel::updateOrCreate([
            'account_id' => $this->currentAccount()->id,
        ], [
            'api_key' => $request->apiKey
        ]);

        return response()->noContent();
    }

    public function getLalamoveSettings()
    {
        return response()->json([
            'lalamove' => Lalamove::where('account_id', $this->currentAccount()->id)->first()
        ]);
    }

    public function storeLalamoveSettings(Request $request)
    {
        Lalamove::updateOrCreate([
            'account_id' => $this->currentAccount()->id,
        ], [
            'api_key' => $request->apiKey,
            'api_secret' => $request->apiSecret,
            'enable_car' => $request->enableCar,
            'car_delivery_desc' => $request->carDeliveryDesc,
            'enable_bike' => $request->enableBike,
            'bike_delivery_desc' => $request->bikeDeliveryDesc
        ]);

        return response()->noContent();
    }

    public function currentAccount(){

        $user = Auth::user();
        $account = Account::where('id',$user->currentAccountId)->first();
        return $account;

    }

    public function getState(Request $request){
            $path = storage_path() . "/json/data.json";
            $API = json_decode(file_get_contents($path), true);

            $states = null;
            foreach($API as $state) {
                if($state['countryName'] == $request->country){
                    $states = $state['regions'];
                    break;
                }
            }
            return response()->json($states);
    }

    public function saveDeliveryHour(Request $request){

		$preferences = EcommercePreferences::updateOrCreate(
            [
				'account_id' => $this->current_accountId()
			],
            [
                'delivery_hour_type' => $request->deliveryHourType,
                'delivery_hour' => json_encode($request->deliveryHours),
                'delivery_disabled_date' => json_encode($request->disableDate),
                'delivery_pre_order_from' => $request->preOrderDay,
                'delivery_is_preperation_time' => $request->isPreperationTime,
                'delivery_is_limit_order' => $request->isLimitOrder,
                'delivery_preperation_value' => $request->preperationValue,
                'delivery_is_daily' => $request->isDaily,
                'delivery_is_same_time' => $request->isSameTime, 
            ]
        );
        return response()->json([
			'status' => 'Success',
			'message' => "Delivery Hour Updated Successfully",

		]);
    }

    public function saveStorePickup(Request $request){
        $preferences = EcommercePreferences::updateOrCreate(
            [
				'account_id' => $this->current_accountId()
			],
            [
                'is_enable_store_pickup' => $request->isEnableStorePickup,
                'pickup_hour' => json_encode($request->deliveryHours),
                'pickup_disabled_date' => json_encode($request->disableDate),
                'pickup_pre_order_from' => $request->preOrderDay,
                'pickup_is_preperation_time' => $request->isPreperationTime,
                'pickup_is_limit_order' => $request->isLimitOrder,
                'pickup_preperation_value' => $request->preperationValue,
                'pickup_is_daily' => $request->isDaily,
                'pickup_is_same_time' => $request->isSameTime, 
            ]
        );
        return response()->json([
			'status' => 'Success',
			'message' => "Store Pickup Updated Successfully",

		]);
    }

    public function getDeliveryPickup(){
        return response()->json([
		    'preferences' => EcommercePreferences::where('account_id',$this->current_accountId())->first()
		]);
    }

    public function showShippingMethods() {
        return Inertia::render('onboarding/pages/ShippingMethodSelection');
    }
    
    public function showShippingSetupForm($type){
        if($type === 'easyparcel') return Inertia::render('app/pages/EasyParcelSettings');
        else if($type === 'lalamove') return Inertia::render('app/pages/LalamoveSettings');
    }

    public function getDefaultCurrency(){
        $account= $this->currentAccount();
        $defaultCurrency = Currency::where(['account_id'=>$account->id,'isDefault'=>1])->first()->currency;
        return $defaultCurrency=='MYR' ? 'RM' : $defaultCurrency;
    }
}

