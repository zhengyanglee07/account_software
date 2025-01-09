<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Promotion\Promotion;
use App\Models\Promotion\PromotionExtraCondition;
use App\Models\Promotion\PromotionFreeShipping;
use App\Models\Promotion\PromotionOrderDiscount;
use App\Models\Promotion\PromotionProductDiscount;
use Illuminate\Support\Carbon;
use Hashids\Hashids;
use Illuminate\Support\Facades\Validator;
use App\ShippingZone;
use App\Account;
use App\Http\Resources\PromotionResource;
use Composer\Console\Application;
use App\Segment;
use App\Traits\AuthAccountTrait;
use App\Currency;
use App\Repository\CheckoutRepository;
use App\Services\Checkout\PromotionService;
use App\Services\RedisService;
use Inertia\Inertia;
class PromotionController extends Controller
{
    use AuthAccountTrait;

    public function viewAllPromotion(){
        $hashids = new Hashids('', 10);
        $accountId = Auth::user()->currentAccountId;
        $account = Auth::user()->currentAccount();
        $allPromotion = Promotion::where('account_id',$accountId)->get();
        $allPromotion->makeHidden(['created_at','updated_at']);

        foreach($allPromotion as $promotion){

            $promotion->promotion_category = ($promotion->promotion_category == 'Shipping') ? 'Free Shipping' : $promotion->promotion_category.' Based';
            $promotion->promotionURL = $hashids->encode($promotion->id);
            $promotion->start_date = Carbon::createFromFormat('Y-m-d H:i:s',$promotion->start_date,'Asia/Kuala_Lumpur')
                                    ->setTimeZone($account->timeZone)
                                    ->format('Y-m-d H:i:s');
            if($promotion->end_date != null){
                $promotion->end_date = Carbon::createFromFormat('Y-m-d H:i:s',$promotion->end_date,'Asia/Kuala_Lumpur')
                                    ->setTimeZone($account->timeZone)
                                    ->format('Y-m-d H:i:s');
            }
        }
        $type = session('type');
        return Inertia::render('promotion/pages/AllPromotion',compact(['allPromotion','type']));
    }

    public function addNewPromotion($promoType,$urlId){

        $accountId = Auth::user()->currentAccountId;
        $account = Account::findOrFail($accountId);
        $currencyStr = ($account->currency == 'MYR') ? 'RM' : $account->currency;
        $timeZone = $account->timeZone;
        $promotionType = ($promoType == 'manual') ? 'manual' : (($promoType == 'automatic') ? 'automatic' : 'undefined');
        $promotion = [];
        $type = ($urlId == 'new') ? 'new' : 'edit';
        $promotionDetail = null;
        if($urlId !== 'new'){
            $hashids = new Hashids('',10);
            $idArr = $hashids->decode($urlId);
            $id = $idArr[0];
            $promotions = Promotion::where('id',$id)->first();
            $promotions->start_date = Carbon::createFromFormat('Y-m-d H:i:s',$promotions->start_date,'Asia/Kuala_Lumpur')
                                    ->setTimeZone($account->timeZone)
                                    ->format('Y-m-d g:i a');
            if($promotions->end_date !== null){
                $promotions->end_date = Carbon::createFromFormat('Y-m-d H:i:s',$promotions->end_date,'Asia/Kuala_Lumpur')
                                        ->setTimeZone($account->timeZone)
                                        ->format('Y-m-d g:i a');
            }
            $promotionDetail = new PromotionResource($promotions);    //restructure in PromotionResource and pass to front end

        }
        return Inertia::render('promotion/pages/AddNewPromotion',compact(['promotionType','promotionDetail','type','currencyStr','timeZone']));
    }

    public function savePromotion(Request $request){
        $validator = $this->promotionValidator($request->all());
        if ($validator->fails()) {
            return response()->json( $validator->errors() , 403);
        }
        $accountId = Auth::user()->currentAccountId;
        if($request->type == 'undefined'){
            return response()->json(['message'=>'Url has been modified. Please cancel and try agian.'],403);
        }

        $existedPromotion = Promotion::where('id',$request->promotionId)->first();
        if(isset($existedPromotion->discount_type)){
            if($existedPromotion->discount_type!= $request->promotionType){
                $existedPromotion->promotionType->delete();
            }
        }
        if($request->promotionType == 'order-discount'){
            $discountType = $this->updatePromotionOrderDiscount($request,$existedPromotion);
        }else if ($request->promotionType == 'free-shipping'){
            $discountType = $this->updatePromotionFreeShipping($request,$existedPromotion);
        }else if ($request->promotionType == 'product-discount'){
            $discountType = $this->updatePromotionProductDiscount($request,$existedPromotion);
        }

        $extraCondition = $this->updatePromotionExtraCondition($request,$existedPromotion);

        $account = Auth::user()->currentAccount();
        $convertStartDate = Carbon::createFromFormat('Y-m-d H:i a',$request->promotionSetting['startDate'],$account->timeZone)
                            ->setTimeZone('Asia/Kuala_Lumpur')
                            ->format('Y-m-d H:i:s');
                            // $endDate = null;
        $endDate = ($request->promotionSetting['endDate']==null) ? null : $request->promotionSetting['endDate'];
        $convertEndDate = null;
        if($endDate !== null){
            $convertEndDate = Carbon::createFromFormat('Y-m-d H:i a',$endDate,$account->timeZone)
                            ->setTimeZone('Asia/Kuala_Lumpur')
                            ->format('Y-m-d H:i:s');
        }
        Promotion::updateOrCreate(
            [
                'id' =>  isset($existedPromotion->id) ? $existedPromotion->id : null,
                'account_id' => $accountId
            ],
            [
                'extra_condition_id' => $extraCondition->id,
                'discount_code' => ($request->type == 'manual') ? $request->promotionSetting['promoCode'] : null,
                'discount_type' => $request->promotionType,
                'discount_id' => $discountType->id,
                'promotion_category' => $request->promotionSetting['discountCategory'],
                'promotion_method' => $request->type,
                'promotion_name' => $request->promotionSetting['promoTitle'],
                'display_name' => $request->promotionSetting['displayTitle'],
                'start_date' => $convertStartDate,
                'end_date' => $convertEndDate,
                'is_expiry' => $request->promotionSetting['isExpiryDate'],
                'promotion_status' => Carbon::parse($convertStartDate)->gt(Carbon::now()) ? 'scheduled'
                    :(($request->promotionSetting['isExpiryDate'] && Carbon::parse($convertEndDate)->lt(Carbon::now()))?'expired':'active'),
            ]
        );

        $request->session()->flash('type', $request->type);
        $request->session()->reflash();

    }
    public function updatePromotionFreeShipping($request,$existedPromotion){

        $zoneId = [];
        foreach($request->promotionSetting['selectedCountries'] as $zone){
            array_push($zoneId,$zone['id']);
        }

        return  PromotionFreeShipping::updateOrCreate(
            [
                'id' => isset($existedPromotion->discount_id) ? $existedPromotion->discount_id : null,
            ],
            [
                'applied_countries_type' => $request->promotionSetting['countryType'],
                'applied_countries' => json_encode($zoneId),
                'requirement_type' => $request->promotionSetting['minimumType'],
                'requirement_value' => $request->promotionSetting['minimumValue'],
                'is_exclude_shipping_rate' => false,
                'exclude_value' => 0,
            ]
        );
    }

    public function updatePromotionOrderDiscount($request,$existedPromotion){
       return PromotionOrderDiscount::updateOrCreate(
            [
                'id' => isset($existedPromotion->discount_id) ? $existedPromotion->discount_id : null,
            ],
            [
                'order_discount_type' => $request->promotionSetting['discountValueType'],
                'order_discount_value' => $request->promotionSetting['discountValue'] ,
                'order_discount_cap' => ($request->promotionSetting['discountValueType'] == 'percentage') ? $request->promotionSetting['discountCap'] : 0,
                'requirement_type' => $request->promotionSetting['minimumType'],
                'requirement_value' => $request->promotionSetting['minimumValue'],
            ]
        );
    }

    public function updatePromotionProductDiscount($request,$existedPromotion){

        $targetValue = [];
        if($request->promotionSetting['productDiscountType'] == 'specific-product'){
            foreach ($request->promotionSetting['selectedProduct'] as $product){
                $tempArr = (array)[];
                $tempArr['productId'] = $product['product']['id'];
                $tempArr['hasVariant'] = $product['product']['hasVariant'];
                $tempArr['combinationVariant'] = [];
                if($product['product']['hasVariant']){
                    foreach($product['combinationVariation'] as $combination){
                        $combArr = (array)[];
                        $combArr['combinationId'] = $combination['combination_id'];
                        $combArr['key'] = $combination['key'];
                        // dump($combArr);
                        array_push($tempArr['combinationVariant'],$combArr);
                    }
                }
                array_push($targetValue,$tempArr);
            }
        }else if($request->promotionSetting['productDiscountType'] == 'specific-category'){
            foreach($request->promotionSetting['selectedCategory'] as $category){
                array_push($targetValue,$category['id']);
            }
        }

        return PromotionProductDiscount::updateOrCreate(
            [
                'id' => isset($existedPromotion->discount_id) ? $existedPromotion->discount_id : null,
            ],
            [
                'product_discount_type' => $request->promotionSetting['discountValueType'],
                'product_discount_value' => $request->promotionSetting['discountValue'] ,
                'product_discount_cap' => ($request->promotionSetting['discountValueType'] == 'percentage') ? $request->promotionSetting['discountCap'] : 0,
                'minimum_quantity' => $request->promotionSetting['minimumQuantity'],
                'requirement_type' => $request->promotionSetting['minimumType'],
                'requirement_value' => $request->promotionSetting['minimumValue'],
                'discount_target_type' => $request->promotionSetting['productDiscountType'],
                'target_value'=> json_encode($targetValue),
            ]
        );
    }

    public function updatePromotionExtraCondition($request,$existedPromotion){
        $targetValue = [];
        foreach($request->promotionSetting['targetValue'] as $value){
            array_push($targetValue,$value['id']);
        }

        return PromotionExtraCondition::updateOrCreate(
            [
                'id' => isset($existedPromotion->extra_condition_id) ? $existedPromotion->extra_condition_id : null,
            ],
            [
                'store_limit_type' => $request->promotionSetting['storeUsageLimitType'],
                'store_limit_value' => $request->promotionSetting['storeUsageValue'],
                'customer_limit_type' => $request->promotionSetting['customerUsageLimitType'],
                'customer_limit_value' => $request->promotionSetting['customerUsageValue'],
                'target_customer_type' => $request->promotionSetting['targetCustomerType'],
                'target_value' => ($request->promotionSetting['targetCustomerType'] == 'all') ? json_encode([]) :json_encode($targetValue),
            ]
        );
    }


    public function deletePromotion(Request $request){
        $promotion = Promotion::firstWhere('id',$request->id);
        $promotion->extraCondition->delete();
        $promotion->promotionType->delete();
        $promotion->delete();
        $request->session()->flash('type', $request->type);
        $request->session()->reflash();

    }

    public function loadDiscount(Request $request, $hasResponse = true)
    {
        $appliedPromotionArray = $request->appliedPromotionArray;
        $totalAfterDiscount = $request->totalAfterDiscount;
        $loadDiscount = [];
        $automatedDiscount = [];
        $latestPromotion = [];
        $shippingCharge = $request->selectedShipping != null  ? $request->selectedShipping['originalCharge'] : 0;
        /**-----declare parameter for promotion validation -----**/
        $param['accountId'] = $request->accountId;
        $param['totalAfterDiscount'] = floatval($totalAfterDiscount);
        $param['customerEmail'] = $request->customerEmail;
        $param['productArray'] = $request->productArray;
        $param['subTotal'] = $request->subTotal;
        $param['country'] = $request->customerCountry;
        $param['isPhysicalProduct'] = $request->isPhysicalProduct;
        $param['customerInfo'] = $request->customerInfo;
        $param['currencyInfo'] = $request->currencyInfo;
        if (!empty($appliedPromotionArray)) {
            foreach ($appliedPromotionArray as $id => $value) {
                $storedDiscount = Promotion::find($value);
                if ($storedDiscount !== null) {
                    if ($storedDiscount->promotion_category !== 'Product') {
                        $tempPromoArr = $this->generalPromotion($request,$storedDiscount,$request->currencyInfo);
                        $tempPromoArr['valid_status'] = $storedDiscount->checkValid($param);
                        $tempPromoArr['discountValue']['valid'] = $tempPromoArr['valid_status'];
                        array_push($loadDiscount, $tempPromoArr);
                    }
                }
            }
        }
        $automatedPromotion = Promotion::where('account_id', $request->accountId)
            ->where('promotion_method', 'automatic')
            ->where('promotion_status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->orderBy('created_at', 'DESC')->get();
        $orderBasedPromotion = $automatedPromotion->where('promotion_category', 'Order');
        $shippingBasedPromotion = $automatedPromotion->where('promotion_category', 'Shipping');
        if (count($orderBasedPromotion) > 0) {
            $latestOrder = $this->getLatestAndNearestMinimumRequirement($orderBasedPromotion, $param);
            if (!empty($latestOrder)) {
                array_push($latestPromotion, $latestOrder);
            }
        }
        if (count($shippingBasedPromotion) > 0) {
            $latestShipping = $this->getLatestAndNearestMinimumRequirement($shippingBasedPromotion, $param);
            if (!empty($latestShipping)) {
                array_push($latestPromotion, $latestShipping);
            }
        }
        if (!empty($latestPromotion)) {
            foreach ($latestPromotion as $promotion) {
                $tempArr = $this->generalPromotion($request,$promotion,$request->currencyInfo);
                $tempArr['discountValue']['type'] = 'automated';
                array_push($automatedDiscount, $tempArr);
            }
        }

        $data = [ 'loadDiscount' => $loadDiscount,'automatedDiscount' => $automatedDiscount, ];


        if($hasResponse){
            return response()->json($data);
        }
        return $data;
    }


    public function getLatestAndNearestMinimumRequirement($promotionArr,$param){
        $nearestValue = 0;
        $latestOrder =(array)[];
        foreach($promotionArr as $promotion){
            if($promotion->checkValid($param)){
                $difference = abs($promotion->promotionType['requirement_value'] - $param['totalAfterDiscount']);
                if($nearestValue == 0){
                    $nearestValue = $difference;
                }
                if($difference <= $nearestValue){
                    $nearestValue = $difference;
                    $latestOrder = $promotion;
                }
            }
        }
        return $latestOrder;
    }


    public function getValidProductPromotion($promotions,$param){
        $validPromotion = [];
        foreach($promotions as $promotion){
            if($promotion->checkValid($param)){
                array_push($validPromotion, $promotion);
            }
        }
        if(count($validPromotion) > 0){
            return $validPromotion[0];
        }else{
            return null;
        }
    }

    public function loadProductDiscount(Request $request, $hasResponse = true)
    {
        $appliedPromotionArray = $request->appliedPromotionArray;
        $loadProductDiscount = [];
        $automatedProductDiscount = [];
        /**-----declare parameter for promotion validation -----**/
        $param['customerEmail'] = $request->customerEmail;
        $param['productArray'] = $request->productArray;
        $param['isPhysicalProduct'] = $request->isPhysicalProduct;

        if ($appliedPromotionArray) {
            foreach ($appliedPromotionArray as $id => $value) {
                $storedDiscount = Promotion::find($value);
                if (isset($storedDiscount)) {
                    if ($storedDiscount->discount_type == 'product-discount') {
                        $tempPromoArr['valid_status'] = $storedDiscount->checkValid($param);
                        $tempPromoArr['promotion'] = $storedDiscount;

                        $discount['type'] = 'manual';
                        $discount['category'] = $storedDiscount->promotion_category;
                        $discount['valid'] = true;
                        $tempPromoArr['promotionProducts'] = [];
                        foreach ($request->productArray as $product) {
                            $param['product'] = $product;
                            if ($storedDiscount->checkValidDiscountProduct($param)) {
                                array_push($tempPromoArr['promotionProducts'], $this->productDiscount($product, $storedDiscount, $request->currencyInfo));
                            }
                        }
                        $totalPromotionPrice = array_reduce($tempPromoArr['promotionProducts'], function ($acc, $item) {
                            return $acc + $item['value'];
                        });
                        $discount['promotionProducts'] = $tempPromoArr['promotionProducts'];
                        $discount['value'] = ($totalPromotionPrice !== null) ? $totalPromotionPrice : 0;
                        $tempPromoArr['discountValue'] = $discount;
                        array_push($loadProductDiscount, $tempPromoArr);
                    }
                }
            }
        }

        $automatedPromotion = Promotion::where('account_id', $request->accountId)
            ->where('promotion_method', 'automatic')
            ->where('promotion_status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->orderBy('created_at', 'DESC')->get();
        $productBasedPromotion = $automatedPromotion->where('promotion_category', 'Product');
        if (count($productBasedPromotion) > 0) {
            $latestValidPromotion = $this->getValidProductPromotion($productBasedPromotion, $param);
            if (isset($latestValidPromotion)) {
                $tempPromoArr['valid_status'] = $latestValidPromotion->checkValid($param);
                $tempPromoArr['promotion'] = $latestValidPromotion;

                $tempPromoArr['promotionProducts'] = [];
                foreach ($request->productArray as $product) {
                    $param['product'] = $product;
                    if ($latestValidPromotion->checkValidDiscountProduct($param)) {
                        array_push($tempPromoArr['promotionProducts'], $this->productDiscount($product, $latestValidPromotion, $request->currencyInfo));
                    }
                }
                $totalPromotionPrice = array_reduce($tempPromoArr['promotionProducts'], function ($acc, $item) {
                    return $acc + $item['value'];
                });
                $discount['category'] = $latestValidPromotion->promotion_category;
                $discount['type'] = 'automated';
                $discount['promotionProducts'] = $tempPromoArr['promotionProducts'];
                $discount['value'] = ($totalPromotionPrice !== null) ? $totalPromotionPrice : 0;
                $discount['valid'] = true;
                $tempPromoArr['discountValue'] = $discount;
                array_push($automatedProductDiscount, $tempPromoArr);
            }
        }

        $data = [ 'loadDiscount' => $loadProductDiscount,'automatedDiscount' => $automatedProductDiscount, ];

        if($hasResponse){
            return response()->json($data);
        }
        return $data;

    }

    public function storeDiscount(Request $request){
        if(empty($request->appliedPromotion)){
            session()->put('appliedPromotion', []);
        }else{
            foreach($request->appliedPromotion as $key => $discount){
                if($discount['promotion']['promotion_category'] == 'Product'){
                    $discount['discountValue']['promotionProducts'] = [];
                    array_push($discount['discountValue']['promotionProducts'],$discount['promotionProducts']);
                }
                $discount['discountValue']['valid'] = $discount['valid_status'];
                $appliedPromotion[$discount['promotion']['id']] = $discount['discountValue'];
                session()->put('appliedPromotion', $appliedPromotion);
                // $isDone = ($key == (count($request->appliedPromotion) - 1));
            }
        }
    }

    public function removeDiscount(Request $request){

    }

    public function applyDiscount(Request $request){
        $accountId = $this->getCurrentAccountId();
        $appliedPromotion = [];
        $promotion = Promotion::findByCode($request->discountCode, $accountId);
        $param['totalAfterDiscount'] = $request->totalAfterDiscount;
        $param['subTotal'] = $request->subtotal;
        $param['customerEmail'] = $request->customerEmail;
        $param['productArray'] = $request->productArray;
        $param['country'] = $request->customerCountry;
        $param['customerInfo'] = $request->customerInfo;
        $param['isPhysicalProduct'] = $request->isPhysicalProduct;
        $param['currencyInfo'] = $request->currencyInfo;
        $errorMessage = null;
        if($promotion == null) {
            $errorMessage = 'Promo code is invalid';
        }else{
            $errorMessage = $promotion->errorMessage($param);
        }
        if($errorMessage !== null){
            return response()->json(['discountError' => $errorMessage] , 403);
        }
        if($promotion->discount_type == 'product-discount'){
            $discount['type'] = 'manual';
            $discount['category'] = $promotion->promotion_category;
            $discount['valid'] = true;
            $appliedProductPromotion['promotionProducts'] = [];
            foreach($request->productArray as $product){
                $param['product'] = $product;
                if($promotion->checkValidDiscountProduct($param)){
                    array_push($appliedProductPromotion['promotionProducts'],$this->productDiscount($product,$promotion,$request->currencyInfo));
                }
            }

            $totalProductPrice = array_reduce($appliedProductPromotion['promotionProducts'],function($acc,$item){return $acc + $item['value'];});
            $discount['value'] = ($totalProductPrice !== null) ? $totalProductPrice : 0;
            $appliedProductPromotion['promotion'] = $promotion;
            $appliedProductPromotion['valid_status'] = true;
            $appliedProductPromotion['discountValue'] = $discount;
            $appliedPromotion = $appliedProductPromotion;
        }else{
            $appliedPromotion = $this->generalPromotion($request,$promotion,$request->currencyInfo);
        }
        return response()->json($appliedPromotion);
    }


    public function productDiscount($product,$promotion,$currencyInfo = null){
            $details['id'] = $product['id'];
            $details['hasVariant'] = $product['hasVariant'];
            $details['variantCombinationID'] = $product['tempid'];
            $details['value'] = floatVal($promotion->discount(($product['productPrice'] * $product['qty']),$currencyInfo,null));
            $valueAfterDiscount = ($product['productPrice'] * $product['qty']) - $details['value'];
            $details['valueAfterDiscount'] = $valueAfterDiscount <=0 ? 0 : $valueAfterDiscount;
            $details['promotion'] = $promotion;
            return $details;
    }

    public function generalPromotion($request,$promotion,$currencyInfo = null){
        $shippingCharge = $request->selectedShipping != null  ? $request->selectedShipping['originalCharge'] : 0;
        $discount['value'] = floatVal($promotion->discount($request->totalAfterDiscount,$currencyInfo,$shippingCharge));
        $discount['type'] = 'manual';
        $discount['category'] = $promotion->promotion_category;
        $discount['valid'] = true;
        $appliedPromotion['promotion'] = $promotion;
        $appliedPromotion['discountValue'] = $discount;
        $appliedPromotion['valid_status'] = true;
        return $appliedPromotion;
    }

    public function getShippingRegion(){
        $accountId = Auth::user()->currentAccountId;
        $shippingZone = ShippingZone::where('account_id',$accountId)->get();

        return response($shippingZone);

    }

    public function promotionValidator(array $data){

        $messages = [
            'promoCode.required' => "Promo code can't be blank",
            'selectedCountries.required' => "Specific countries must be added",
            'minimumValue.required'=>"Minimum purchase can’t be blank",
            'discountValue.required'=>"Discount value can’t be blank",
            'discountCap.required'=>"Discount cap can’t be blank",
            'storeUsageValue.required' =>"Total usage limit can’t be blank",
            'customerUsageValue.required' =>"Customer usage limit can’t be blank",
            'minimumQuantity.required' => 'Required*',
            'minimumQuantity.gt' => 'Must be greater than 0',
            'targetValue.required' => 'Please select target customer group'
        ];

        $attribute = [
            'promoTitle' => 'promotion title',
            'displayTitle' => 'promotion display title'
        ];

        $validator = Validator::make($data['promotionSetting'], [
            'promoTitle' => 'required',
            'displayTitle' => 'required',
        ],$messages,$attribute);
        $validator->sometimes('promoCode',['required','unique:promotions,discount_code,NULL,id,account_id,'.Auth::user()->currentAccountId  .$data['promotionId']],function($input){
            return $input->promotionType  == 'manual';
        });
        $validator->sometimes('selectedCountries','required',function($input){
            return $input->countryType == 'selectedCountries';
        });
        $validator->sometimes('minimumValue',['required','gt:0','numeric'],function($input){
            return $input->minimumType != 'none';
        });
        $validator->sometimes('minimumQuantity',['required','gt:0','numeric'],function($input){
            return $input->discountType == 'productBased';
        });
        $validator->sometimes(['discountValue'],'required',function($input){
            return $input->discountCategory != 'Shipping';
        });
        $validator->sometimes('discountValue',['numeric','between:0,100'],function($input){
            return $input->discountValueType == 'percentage';
        });
        $validator->sometimes('storeUsageValue','required',function($input){
            return $input->storeUsageLimitType == 'limited';
        });
        $validator->sometimes('customerUsageValue','required',function($input){
            return $input->customerUsageLimitType == 'limited';
        });
        $validator->sometimes('targetValue','required',function($input){
            return $input->targetCustomerType == 'specific-segment';
        });

        return $validator;
    }

    public function getAllSegments(){
        $accountId = Auth::user()->currentAccountId;
        $allSegment = Segment::where('account_id',$accountId)->get();
        return response($allSegment);

    }

    public function loadFreeShippingDiscount(Request $request){
        dd($request);

    }

    public function checkPromotionValid(Request $request){
        dd($request);
    }


    public function loadAllDiscount(Request $request){
        $accountId = $this->getCurrentAccountId();
        $currencyDetails = Currency::where('account_id', $accountId)->where('isDefault', 1)->first();
        $request['accountId'] = $accountId;
        $request['currencyInfo'] = $currencyDetails;
        $loadDiscount = $this->loadDiscount($request, false);
        $loadProductDiscount = $this->loadProductDiscount($request, false);

        return response()->json([
            'loadDiscount' => $loadDiscount,
            'loadProductDiscount' => $loadProductDiscount,
        ]);
    }

    /*===============================================================================================================*/
    /*                                         Currently in use started on 11/08/2022                                */
    /*===============================================================================================================*/
    public function applyPromotion(Request $request)
    {
        $promotionService = new PromotionService();
        $isApplied = $promotionService->checkIsPromoCodeApplied($request->discountCode);
        if ($isApplied) return abort(422, 'Promo code already applied!');

        $promotion = $promotionService->applyPromotion($request->discountCode);
        if (!empty($promotion['error']))
            return abort(422, $promotion['error']);

        $promoIds = $promotionService->getAvailablePromotionIds($promotion['data']);
        RedisService::set('promotion', $promoIds);

        $checkoutRepository = new CheckoutRepository();
        return response()->json([
            'promotion' => $checkoutRepository::$availablePromotions,
            'cartItems' => $checkoutRepository::$cartItemsWithPromotion,
            'cashback' => $checkoutRepository::$cashback,
            'storeCredit' => $checkoutRepository::$storeCredit,
        ]);
    }

    public function deleteAppliedPromotion(Request $request)
    {
        $promotionIdToDelete = (int)$request->promotionId;
        $promotionIds = RedisService::get('promotion');
        $filteredPromotionids = array_filter($promotionIds, function ($ids) use ($promotionIdToDelete) {
            return $ids !== $promotionIdToDelete;
        });
        RedisService::set('promotion', $filteredPromotionids);

        $checkoutRepository = new CheckoutRepository();
        return response()->json([
            'promotion' => $checkoutRepository::$availablePromotions,
            'cartItems' => $checkoutRepository::$cartItemsWithPromotion,
            'cashback' => $checkoutRepository::$cashback,
            'storeCredit' => $checkoutRepository::$storeCredit,
        ]);
    }
}
