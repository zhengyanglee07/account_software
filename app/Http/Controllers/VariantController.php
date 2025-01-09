<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Variant;
use App\VariantValue;
use App\ProductVariant;
use App\VariantDetails;
use App\UsersProduct;
use Auth;

class VariantController extends Controller
{
    public function current_accountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    // Get All Shared Variants
    public function getSharedVariants () {
        $allSharedVariants = Variant::where([
            ['account_id', $this->current_accountId()],
            ['is_shared', 1],
        ])->get();
        $values = array();

        if(count($allSharedVariants) !== 0) {
            foreach ($allSharedVariants as $sharedVariant) {
                $count = ProductVariant::all()->where('variant_id', $sharedVariant->id)->count();
                $obj = (object)[
                    'id' => $sharedVariant->id,
                    'count' => $count,
                    'variant_name' => $sharedVariant->variant_name,
                    'type' => $sharedVariant->type,
                    'values' => $sharedVariant->values,
                    'display_name' => $sharedVariant->display_name,
                ];
                array_push($values, $obj);
            }
        }

        $values = json_encode($values);

        return view('sharedProductOption', compact('values'));
    }

    // Get add variant page
    public function getVariantModal() {
        // $variant = Variant::where([
        //     ['account_id', $this->current_accountId()],
        //     ['id', $id]
        // ])->first();
        // $variantValues = array();
        // $default= '';
        // $deletable = 0;

        // if($variant === null) {
        //     $variantValues = null;
        // } else {
        //     // Check if safe to delete
        //     $count = ProductVariant::all()->where('variant_id', $variant->id)->count();
        //     if($count === 0) {
        //         $deletable = 1;
        //     }
        //     $variantValues = $variant->values;
        //     $default = $variantValues->where('default', 1)->first();
            
        //     if($default === null) {
        //         $default = '';
        //     } else {
        //         $default = $default->variant_value;
        //     }
        // }

        // * For Modal
        $allSharedVariants = Variant::where([
            ['account_id', $this->current_accountId()],
            ['is_shared', 1],
        ])->get();

        $values = array();

        if(count($allSharedVariants) !== 0) {
            foreach ($allSharedVariants as $sharedVariant) {
                $count = ProductVariant::all()->where('variant_id', $sharedVariant->id)->count();
                $obj = (object)[
                    'id' => $sharedVariant->id,
                    'count' => $count,
                    'variant_name' => $sharedVariant->variant_name,
                    'type' => $sharedVariant->type,
                    'values' => $sharedVariant->values,
                    'display_name' => $sharedVariant->display_name,
                ];
                array_push($values, $obj);
            }
        }

        //$allSharedVariants = json_encode($allSharedVariants);

        return response()->json(['variants'  => $values]);
    }

    // Add Shared Variant and it's VariantValue
    public function addSharedVariant (Request $request) {
        /**
         ** $request->input('variant_name') : String
         *      variant_name: 'Color',
         *
         ** $request->input('type') : String
         *      type: 'swatch',
         *
         ** $request->input('values') : Array
         *      values: [
         *          { variant_value: 'Red', image_url: 'test', color: '#fff', default: 0 }
         *      ],
         */

        $checkExisting = Variant::where([
            ['variant_name', $request->input('variant_name')],
            ['account_id', $this->current_accountId()],
            ['is_shared', 1]
        ])->first();

        if($checkExisting !== null) {
            return response()->json(['status' => 'Error : Variant exist in database!']);
        }
        $latestVariant = Variant::where(['account_id'=>$this->current_accountId(), 'is_shared'=>true])->latest('global_priority')->first();
        $priorityCount = 1;
        if($latestVariant) $priorityCount = $latestVariant->global_priority ? $latestVariant->global_priority + 1 : 1;
        $variant = Variant::create([
            'variant_name' => $request->input('variant_name'),
            'account_id' => $this->current_accountId(),
            'is_shared' => 1,
            'type' => $request->input('type'),
            'display_name' => $request->input('display_name'),
            'global_priority' => $priorityCount,
        ]);

        foreach ($request->input('values') as $value) {
            $value = (object) $value;

            VariantValue::create([
                'variant_id' => $variant->id,
                'variant_value' => $value->variant_value,
                'image_url' => $value->image_url,
                'color' => $value->color,
                'default' => $value->default,
            ]);
        }

        return response()->json(['status' => 'Success']);
    }

    // Delete Shared Variants
    public function deleteSharedVariantValue ($id) {
        /**
         * $request->input('id') : id
         */

         $variant = Variant::find($id);
         if ($variant === null) {
             return response()->json(['status' => 'Error : Variant not found!']);
         }

         $variant->delete();

         return response()->json(['status' => 'success']);
    }

    // Update Shared Variants
    public function updateVariants (Request $request) {
        /**
         **  $request->input('updatedVariants') : Array
         *   [
         *      {
         *          id: 67,
         *          variant_name: 'Color',
         *          type: 'dropdown',
         *          values:
         *               [
         *                   {
         *                       id: 67,
         *                       variant_value: 'Red',
         *                   },
         *               ],
         *      },
         *   ]
         **  $request->input('deletedVariantValues') : Array
         *   [ 1, 2, 3 ]
         *
         *
         *
         **   There are total of 4 steps to update Global Variant(s) :
         *       First - Delete all the Variant Details for all the 'deletedVariantValuesId'
         *       Second - Delete the value if 'deletedVariantValues' not empty.
         *       Third - Update the value if found, otherwise create new data.
         *       Forth - Update the value according to the order.
        */

        $variant = $request->input('updatedVariants');
        $variant = (object) $variant;

        $checkExisting = Variant::where([
            ['variant_name', $variant->variant_name],
            ['account_id', $this->current_accountId()],
            ['is_shared', 1]
        ])->first();

        if($checkExisting !== null && $checkExisting->id !== $variant->id) {
            return response()->json(['status' => 'Error : Variant exist in database!']);
        }

        if(count($request->input('deletedVariantValues')) !== 0) {

            $products = UsersProduct::where([
                ['account_id', $this->current_accountId()],
                ['hasVariant', 1]
            ])->get();

            // First Step
            if(count($products) !== 0) {
                foreach ($products as $product) {

                    foreach ($request->input('deletedVariantValues') as $id) {
                        $product->variant_details()
                                ->where('option_1_id', $id)
                                ->orWhere('option_2_id', $id)
                                ->orWhere('option_3_id', $id)
                                ->orWhere('option_4_id', $id)
                                ->orWhere('option_5_id', $id)->delete();
                    }
                }
            }

            // Second Step
            foreach ($request->input('deletedVariantValues') as $id) {
                VariantValue::find($id)->delete();
            }
        }

        // Third Step

        $variant = $request->input('updatedVariants');
        $variant = (object) $variant;
        //return response()->json(['id' => $variant->id]);
        $selectedVariant = Variant::find($variant->id);

        if($selectedVariant === null) {
            return response()->json(['status' => 'Error : Data not found!']);
        }

        $selectedVariant->update([
            'variant_name' => $variant->variant_name,
            'type' => $variant->type,
            'display_name' => $variant->display_name
        ]);

        foreach ($variant->values as $updatedValue) {
            $updatedValue = (object) $updatedValue;
            $updates = VariantValue::find($updatedValue->id);

            if ($updates === null) {
                VariantValue::create([
                    'variant_id' => $selectedVariant->id,
                    'variant_value' => $updatedValue->variant_value,
                    'image_url' => $updatedValue->image_url,
                    'color' => $updatedValue->color,
                    'default' => $updatedValue->default,
                ]);
            } else {
                $updates->update([
                    'variant_value' => $updatedValue->variant_value,
                    'image_url' => $updatedValue->image_url,
                    'color' => $updatedValue->color,
                    'default' => $updatedValue->default,
                ]);
            }
        }

        $selectedVariantValues = $selectedVariant->values;
        foreach ($variant->values as $key=>$updatedValue) {
            $updatedValue = (object) $updatedValue;
            $selectedVariantValues[$key]->variant_value = $updatedValue->variant_value;
            $selectedVariantValues[$key]->image_url = $updatedValue->image_url;
            $selectedVariantValues[$key]->color = $updatedValue->color;
            $selectedVariantValues[$key]->default = $updatedValue->default;
            $selectedVariantValues[$key]->save();
        }

        return response()->json(['status' => 'success']);
    }

    public function updateVariantPriority(Request $request){
        // dd($request);
        foreach($request->items as $key=>$item){
            // dd($item);
            $variant = Variant::find($item['id']);
            if(!$variant) {
                return response()->json(['status' => 'Error : Something went wrong!']);
            }
            $variant->global_priority = $key + 1;
            $variant->save();
        }
        $allSharedVariants = Variant::where([
            ['account_id', $this->current_accountId()],
            ['is_shared', 1],
        ])->orderBy('global_priority')->get();
        $values = array();

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
                array_push($values, $obj);
            }
        }
        $variantValues=json_encode($values);
        return response()->json(['status' => 'Success', 'items' => $variantValues ]);

    }
}
