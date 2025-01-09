<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UsersProductController;
use Auth;
use App\productOption;
use App\productOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Variant;
use App\VariantValue;
use App\Currency;
use App\ProductVariant;
use App\UsersProduct;
use Inertia\Inertia;

class ProductOptionController extends Controller
{
    public function current_accountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    public function getCustomization(){
        $productOptionArray = [];
        $productOptions =
        productOption::where('account_id',$this->current_accountId())
            ->where('is_shared', '1')->select('name', 'display_name', 'type', 'id')->orderBy('global_priority')
            ->get();
        foreach ($productOptions as $key => $productOption) {
            $productArray = DB::table('product_option_users_product')->where('product_option_id', $productOption->id)->get()->map(function($productOption){
                return $productOption->users_product_id;
            });
            $productOptionValueArray = [];
            $productOptionValue = productOptionValue::where('product_option_id', $productOption->id)->get();
            foreach ($productOptionValue as $key => $value) {
                array_push($productOptionValueArray, $value->label);
            }
            $productOptionObject = (object)[
                'id' => $productOption->id,
                'name' => $productOption->name,
                'display_name' => $productOption->display_name,
                'type' => $productOption->type,
                'label' => join(", ", $productOptionValueArray),
                'noOfProduct' => $productArray->count(),
                'productIds' => $productArray
            ];
            array_push($productOptionArray, $productOptionObject);
        }
        return $productOptionArray;
    }

    public function getAllProductOption()
    {
        $productOptionArray = [];
        $productOptionArray = $this->getCustomization();
        // Get All Shared Variants
        $allSharedVariants = Variant::where([
            ['account_id', $this->current_accountId()],
            ['is_shared', 1],
        ])->orderBy('global_priority')->get();
        $variants = array();

        if(count($allSharedVariants) !== 0) {
            foreach ($allSharedVariants as $sharedVariant) {
                $count = ProductVariant::all()->where('variant_id', $sharedVariant->id)->count();
                $obj = (object)[
                    'id' => $sharedVariant->id,
                    'count' => $count,
                    'variant_name' => $sharedVariant->variant_name,
                    'display_name' => $sharedVariant->display_name,
                    'type' => $sharedVariant->type,
                    'values' => $sharedVariant->values,
                ];
                array_push($variants, $obj);
            }
        }

        $currency = Currency::where('account_id',$this->current_accountId())->where('isDefault',1)->first()->prefix;
        $UsersProductController = new UsersProductController();
        $products = UsersProduct::where('account_id', $this->current_accountId())->get();
        $products = $UsersProductController->restructureProducts($products, $currency);

        return Inertia::render('product/pages/ProductOptions', compact('productOptionArray', 'variants','currency','products'));
    }



    public function getProductOption()
    {
        $productOptions = productOption::where('account_id',$this->current_accountId())->where('is_shared', '1')->orderBy('global_priority')->get();
        $productOptionArray = [];
        foreach($productOptions as $productOption){
            $productArray = count(DB::table('product_option_users_product')->where('product_option_id', $productOption->id)->get());
            $productOptionValue = productOptionValue::where('product_option_id',$productOption->id)->get();
            $productOption = [
                "id" => $productOption->id,
                "name" => $productOption->name,
                "display_name" => $productOption->display_name,
                "type" => $productOption->type,
                "help_text" => $productOption->help_text,
                "tool_tips" => $productOption->tool_tips,
                "is_range" => $productOption->is_range,
                "up_to" => $productOption->up_to,
                "at_least" => $productOption->at_least,
                "is_shared" => $productOption->is_shared,
                "is_required" => $productOption->is_required,
                "is_total_Charge" => $productOption->is_total_Charge,
                "global_priority" => $productOption->global_priority,
                "total_charge_amount" => $productOption->total_charge_amount,
                "sort_order" => $productOption->sort_order,
                'noOfProduct' => $productArray,
                'inputs' =>  $productOptionValue,
            ];
            array_push($productOptionArray,$productOption);
        }
        return response($productOptionArray);
    }

    public function getExistsOption($type)
    {
        $productOptions = productOption::where('account_id',$this->current_accountId())
            ->where('is_shared', '1')
            ->pluck('name');
        if(isset($type)){
            $productOptions = productOption::where('account_id',$this->current_accountId())
            ->where('id','!=',$type)
            ->where('is_shared', '1')
            ->pluck('name');
        }
        return response($productOptions);
    }

    public function addProductOption($type)
    {
        $productOptionArray = [];
        if ($type !== 'create') {
            $productOption = productOption::where('id', $type)->first();
            $productOptionValue = productOptionValue::where('product_option_id', $productOption->id)->get();
            $productOption = [
                "id" => $productOption->id,
                'account_id' => $this->current_accountId(),
                "name" => $productOption->name,
                "display_name" => $productOption->display_name,
                "type" => $productOption->type,
                "help_text" => $productOption->help_text,
                "tool_tips" => $productOption->tool_tips,
                "is_range" => $productOption->is_range,
                "up_to" => $productOption->up_to,
                "at_least" => $productOption->at_least,
                "is_shared" => $productOption->is_shared,
                "is_required" => $productOption->is_required,
                "is_total_Charge" => $productOption->is_total_Charge,
                "global_priority" => $productOption->global_priority,
                "total_charge_amount" => $productOption->total_charge_amount,
                "sort_order" => $productOption->sort_order,
                'inputs' => $productOptionValue,
            ];
            array_push($productOptionArray, $productOption);
        }
        $currency = Currency::where('account_id',$this->current_accountId())
        ->where('isDefault',1)->first()->currency;
        $optionType = 'shared';
        return view('newSharedProductOption', compact('type', 'optionType', 'productOptionArray','currency'));
    }

    public function saveSharedProductOption(request $request)
    {
        // dd($request);
        $productOptionArray = [];
        if(isset($request->deletedInputOptionsId)) {
                productOption::whereIn('id', $request->deletedInputOptionsId)->delete();
        }
        if (count($request->deletedInputId) !== 0 && isset($request->deletedInputId) && $request->deletedInputId !== null) {
            productOptionValue::whereIn('id', $request->deletedInputId)->delete();
        }
        if (count($request->productOption) > 0) {
            foreach ($request->productOption as $key => $data) {
                $latestOption = productOption::where(['account_id'=> $this->current_accountId(), 'is_shared'=>true])->latest('global_priority')->first();
                $priorityCount = 1;
                if($latestOption) $priorityCount = $latestOption->global_priority ? $latestOption->global_priority + 1  : 1;;
                isset($data['id']) === false ? $data['id'] = 0 : $data['id'];
                $productOption = productOption::updateOrCreate(
                    ['id' => $data['id']],
                    [
                        'account_id' => $this->current_accountId(),
                        'name' => $data['name'] ?? '',
                        'display_name' => $data['display_name'],
                        'type' => $data['type'],
                        'help_text' => $data['help_text'],
                        'tool_tips' => $data['tool_tips'],
                        'is_range' => $data['is_range'],
                        'up_to' => $data['up_to'],
                        'at_least' => $data['at_least'],
                        'is_required' => $data['is_required'],
                        "is_total_Charge" => $data['is_total_Charge'],
                        "global_priority" => $priorityCount,
                        "total_charge_amount" => $data['total_charge_amount'],
                        'is_shared' => true,
                        'sort_order' => $key,
                    ]
                );
                foreach ($data['inputs'] as $key => $data) {
                    isset($data['id']) === false ? $data['id'] = 0 : $data['id'];
                    // $data['single_charge'] = $data['type_of_single_charge'] === 'Default' ? 0.00 : $data['single_charge'];
                    productOptionValue::updateOrCreate(
                        ['id' => $data['id']],
                        [
                            'product_option_id' => $productOption->id,
                            'label' => $data['label'],
                            'is_default' => $data['is_default'],
                            'option' => $data['option'],
                            'sort_order' => $key,
                            'type_of_single_charge' => $data['type_of_single_charge'],
                            'single_charge' => $data['single_charge'],
                            'value_1' => $data['value_1'],
                        ]
                    );
                };
                forEach($request->productIds as $id){
                    DB::table('product_option_users_product')->updateOrInsert(
                        [
                            'users_product_id' => $id,
                            'product_option_id' => $productOption->id
                        ]);
                };
                $productOptionArray = $this->getCustomization();
                return response($productOptionArray);
            }
        }
    }
    public function deleteSharedProductOption($id)
    {
        $productOptionArray = [];
        productOptionValue::where('product_option_id', $id)->delete();
        productOption::where('id', $id)->delete();
        $productOptions = productOption::where('account_id',$this->current_accountId())
        ->where('is_shared', '1')
        ->select('name', 'display_name', 'type', 'id')
        ->get();
        $productOptionArray = $this->getCustomization();
        return response($productOptionArray);
    }

    public function productsUpdateOption($type, Request $request){
        foreach($request->productIds as $id){
            $product = UsersProduct::find($id);
            if(!$product) {
                return response()->json(['status' => 'Error : Something went wrong!']);
            }
            $product->product_option()->detach($request->optionIds);
            if($type==='add') $product->product_option()->attach($request->optionIds);
        }
        return response()->json(['status' => 'Success']);
    }

    public function updateCustomizationPriority(Request $request){
        // dd($request);
        foreach($request->items as $key=>$item){
            // dd($item);
            $productOption = productOption::find($item['id']);
            if(!$productOption) {
                return response()->json(['status' => 'Error : Something went wrong!']);
            }
            $productOption->global_priority = $key + 1;
            $productOption->save();
        }
        $customizations = $this->getCustomization();
        return response()->json(['status' => 'Success', 'items' => $customizations ]);

    }
}
