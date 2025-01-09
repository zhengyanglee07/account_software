<?php

namespace App\Http\Controllers;

use Auth;
use App\Tax;
use App\Badges;
use App\Account;
use App\Variant;
use App\Category;
use App\Currency;
use App\OrderDetail;
use App\SaleChannel;
use App\UsersProduct;
use App\VariantValue;
use App\image_gallery;
use App\productOption;
use App\ProductReview;
use App\ProductVariant;
use App\VariantDetails;
use App\ProductCategory;
use App\productOptionValue;
use Illuminate\Http\Request;
use App\ProductReviewSetting;
use App\Traits\UsersProductTrait;
use Illuminate\Support\Facades\DB;
use App\Traits\CurrencyConversionTraits;
use App\Http\Controllers\ProductSubscriptionController;
use App\AccountDomain;
use App\Http\Resources\ProductCourseResource;
use App\Services\ProcessedContactService;
use App\Services\ProductAccessService;
use App\Services\ProductCourseService;
use App\Traits\AuthAccountTrait;
use Inertia\Inertia;

use App\Traits\DatatableTrait;

class UsersProductController extends Controller
{
    use CurrencyConversionTraits, UsersProductTrait, AuthAccountTrait, DatatableTrait;

    public function generateReferenceKey()
    {
        $condition = true;
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = UsersProduct::where([
                ['account_id', $this->getCurrentAccountId()],
                ['reference_key', $randomId]
            ])->exists();
        } while ($condition);
        return $randomId;
    }

    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }

    public function restructureProducts($products, $currency)
    {
        foreach ($products as $product) {
            $product->displayPrice = ($currency === 'MYR' ? 'RM' : $currency) . " " . $this->priceFormater($product->productPrice);

            if ($product->hasVariant) {
                $minimunVariantPrice = $product->variant_details->min('price');
                $product->displayPrice = "From " . ($currency === 'MYR' ? 'RM' : $currency) . " " .   $this->priceFormater($minimunVariantPrice);
            }
            $selectedSaleChannels = [];
            foreach ($product->saleChannels()->get() as $saleChannel) {
                $selectedSaleChannels[] = $saleChannel->type;
            };
            if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
            $product['selectedSaleChannels'] =  $selectedSaleChannels;
        }
        return $products;
    }

    public function getProductDashboardPage(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $currency = Account::find($currentAccountId)->currency;
        $exceptDefault = Currency::where('account_id', $this->getCurrentAccountId())
            ->where('isDefault', '0')
            ->where('exchangeRate', '!=', null)
            ->get();

        $status = $request->query('status') ?? 'active';
        $paginatedProducts = $this->paginateRecords(
            UsersProduct::where(['account_id' => $currentAccountId, 'status' => $status]),
            $request->query(),
            ['title', 'price', 'type'],
        );
        $products = $paginatedProducts->getCollection();

        $products = $this->restructureProducts($products, $currency);

        $appSaleChannel = new SaleChannel();
        $activeSaleChannels = $appSaleChannel->getActiveSaleChannels($currentAccountId) ?? [];
        $categories = $this->getAllCategoriesOrderByPriority($currentAccountId);
        if (count($categories) === 0) {
            $categories[] = ['name' => 'Sorry, No Result', 'id' => ''];
        }
        $sharedOptions = productOption::where(['account_id' => $currentAccountId, 'is_shared' => true])->get();

        foreach ($sharedOptions as $sharedOption) {
            $sharedOption->inputs =  productOptionValue::where('product_option_id', $sharedOption->id)->get();
        }

        $environment = app()->environment();
        $accountDomains = AccountDomain::whereNotNull('type')->get()->filter(function ($value) use ($activeSaleChannels) {
            return in_array($value->type, $activeSaleChannels);
        });
        $storeDomain = $accountDomains->where('type', 'online-store')->first()?->domain ?? $accountDomains->where('type', 'mini-store')->first()?->domain;

        return Inertia::render('product/pages/ProductDashboardPage', compact(
            'products',
            'currency',
            'exceptDefault',
            'activeSaleChannels',
            'categories',
            'sharedOptions',
            'environment',
            'storeDomain',
            'paginatedProducts',
        ));
    }

    public function generatePathName($product, $path)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $tempProducts = UsersProduct::where('account_id', $currentAccountId)->get();
        $pathName = (string) $path;
        $pathNameBuffer = $pathName;
        $tempPathsArray = array();

        foreach ($tempProducts as $temp) {
            if ($product['id'] !== $temp['id']) {
                array_push($tempPathsArray, $temp['path']);
            }
        }

        $target = 0;
        while (in_array($pathName, $tempPathsArray)) {
            $target += 1;
            $pathName = $pathNameBuffer . '-' . $target;
        }

        return $pathName;
    }

    public function addProduct(request $request)
    {
        $isNewProduct = $request->type === "new";
        $currentAccountId = $this->getCurrentAccountId();
        $request->validate([
            'productTitle' => 'required',
            'path' => 'required',
        ]);
        $product = UsersProduct::where([
            ['id', $request->type],
            ['account_id', $currentAccountId]
        ])->firstOr(function () {
            return UsersProduct::create([
                'title' => "",
                'account_id' => $this->getCurrentAccountId(),
                'reference_key' => $this->generateReferenceKey(),
                'product_combination_id' => $this->getRandomId('products', 'product_combination_id')
            ]);
        });
        $productPathChanged = strtolower((string) $request->path) !== strtolower((string) $product['path']);
        $product->update([
            'status' => $request->input('details.status'),
            'title' => $request->input('details.title'),
            'description' => $request->input('details.description'),
            'image_url' => $request->input('details.imagePath'),
            'image_collection' => $request->input('details.imageCollection'),
            'price' => $request->input('details.price') ?? 0.00,
            'compare_price' => $request->input('details.comparePrice'),
            'type' => $request->input('details.type'),
            'weight' => $request->input('details.weight') ?? 0.00,
            'sku' => $request->input('details.SKU') ?? null,
            'is_selling' => $request->input('details.is_selling') ?? false,
            'quantity' => $request->input('details.quantity') ?? 0,
            'is_taxable' => $request->input('details.isTaxable'),
            'width' => $request->input('details.width'),
            'length' => $request->input('details.length'),
            'height' => $request->input('details.height'),
            'path' => $productPathChanged ? $this->generatePathName($product, $request->path) : $request->path,
            'meta_title' => $request->input('seo.title') ?? null,
            'meta_description' => $request->input('seo.description') ?? null,
            'access_period' => $request->input('details.coursePeriod') ?? null,
            'asset_url' => $request->input('details.asset_url') ?? null,
        ]);

        // if($productNameChanged) {
        //     $product->update([
        //         'path' => $request->input('seo.path') ?? $this->generatePathName($product, $request->input('details.title')),
        //     ]);
        // }


        $product->variant_details()->delete();


        // Delete Action before Create/Update
        if ($request->deletedVariantId || $request->deletedVariantValueId || $request->deletedVariantIdForProductVariation) {
            dump('delete varaint');
            if (count($request->deletedVariantValueId) !== 0) {
                foreach ($request->deletedVariantValueId as $id) {
                    VariantValue::find($id)->delete();
                }
            }

            if (count($request->deletedVariantId) !== 0) {
                foreach ($request->deletedVariantId as $id) {
                    $checkIfExist = $product->product_variants->where('variant_id', $id)->first();
                    if ($checkIfExist !== null) {
                        $checkIfExist->delete();
                    }
                    $selectedVariantToDelete = Variant::find($id);

                    if ($selectedVariantToDelete->is_shared === 0) {
                        $selectedVariantToDelete->values()->delete();
                        $selectedVariantToDelete->delete();
                    }
                }
            }

            if (count($request->deletedVariantIdForProductVariation) !== 0) {
                foreach ($request->deletedVariantIdForProductVariation as $id) {
                    $checkIfExist = $product->product_variants->where('variant_id', $id)->first();
                    if ($checkIfExist !== null) {
                        $checkIfExist->delete();
                    }
                }
            }
        }

        // Create/Update Product Category
        if ($request->deletedCategories) {
            foreach ($request->deletedCategories as $id) {
                // return response()->json(['status' => $id]);
                $productCategory = ProductCategory::where('product_id', $product['id'])->where('category_id', $id)->first();
                if ($productCategory !== null) {
                    $productCategory->delete();
                }
            }
        }
        if ($request->categories) {
            foreach ($request->categories as $category) {
                $newProductCategory = ProductCategory::firstOrCreate([
                    'product_id' => $product['id'],
                    'category_id' => $category['id']
                ]);
            }
        }

        // Create/Update Variant, Variant Values, Product Variant and Variant Details
        if ($request->variantValue) {
            ProductVariant::where('product_id', $product->id)->delete();
            foreach ($request->variantValue as $variantObj) {

                // If it is New Product
                if ($isNewProduct) {
                    // Create New Variant if it is not Shared Variant
                    $newVariant = Variant::firstOrCreate([
                        'id' => $variantObj['id'],
                        'variant_name' => $variantObj['name'],
                        'account_id' => $this->getCurrentAccountId(),
                        'is_shared' => $variantObj['is_shared'],
                        'type' => $variantObj['type'],
                    ]);

                    // Create New Variant Value if it is not Shared Variant
                    if (!$newVariant->is_shared) {
                        foreach ($variantObj['valueArray'] as $key => $value) {
                            $value = (object) $value;
                            $variant_value = VariantValue::firstOrCreate([
                                'id' => $value->id,
                                'variant_id' => $newVariant->id,
                                'variant_value' => $value->variant_value,
                                'default' => $value->default,
                                'color' => $value->color,
                                'image_url' => $value->image_url,
                            ]);
                        }
                    }

                    // Create Product Variant
                    $product_variant = ProductVariant::firstOrCreate([
                        'variant_id' => $newVariant->id,
                        'product_id' => $product->id,
                    ]);
                } else {
                    // If it is existing Product
                    $newVariant = Variant::updateOrCreate(
                        ['id' => $variantObj['id']],
                        [
                            'variant_name' => $variantObj['name'],
                            'account_id' => $this->getCurrentAccountId(),
                            'is_shared' => $variantObj['is_shared'],
                            'type' => $variantObj['type'],
                        ]
                    );
                    if ($newVariant->is_shared === false || $newVariant->is_shared === 0) {
                        foreach ($variantObj['valueArray'] as $key => $value) {
                            $value = (object) $value;
                            $variant_value = VariantValue::updateOrCreate(
                                ['id' => $value->id],
                                [
                                    'variant_id' => $newVariant->id,
                                    'variant_value' => $value->variant_value,
                                    'default' => $value->default,
                                    'color' => $value->color,
                                    'image_url' => $value->image_url,
                                ]
                            );
                        }

                        // Update Value for Reorder purpose
                        $newVariantValues = $newVariant->values;
                        foreach ($variantObj['valueArray'] as $key => $value) {
                            $value = (object) $value;
                            $newVariantValues[$key]->variant_value = $value->variant_value;
                            $newVariantValues[$key]->default = $value->default;
                            $newVariantValues[$key]->color = $value->color;
                            $newVariantValues[$key]->image_url = $value->image_url;
                            $newVariantValues[$key]->save();
                        }
                    }
                    $product_variant = ProductVariant::updateOrCreate(
                        [
                            'variant_id' => $newVariant->id,
                            'product_id' => $product->id,
                        ]
                    );
                }
            }


            foreach ($request['optionValue'] as $key => $value) {
                $allVariantName = $request->combinationVariantName[$key];
                $price = (float) $value['price'];
                $compared_at_price = $value['compared_at_price'];
                $weight = (float) $value['weight'];
                $variantDetailRef = $value['ref'];

                VariantDetails::create([
                    'reference_key' => $variantDetailRef == null ? $this->generateReferenceKey() : $variantDetailRef,
                    'product_id' => $product->id,
                    'option_1_id' => $this->findVariantValueId($product->id, $allVariantName['option_1']),
                    'option_2_id' => $this->findVariantValueId($product->id, $allVariantName['option_2']),
                    'option_3_id' => $this->findVariantValueId($product->id, $allVariantName['option_3']),
                    'option_4_id' => $this->findVariantValueId($product->id, $allVariantName['option_4']),
                    'option_5_id' => $this->findVariantValueId($product->id, $allVariantName['option_5']),
                    'sku' => $value['sku'],
                    'price' => $price,
                    'comparePrice' => $compared_at_price,
                    'weight' => $weight,
                    'image_url' => $value['image_url'],
                    'quantity' => $value['quantity'],
                    'is_selling' => $value['is_selling'],
                    'is_visible' => $value['is_visible'],
                ]);
            }
        }
        if ($request->isCustomizationUpdated || $request->isVariantUpdated) {
            $product->update([
                'product_combination_id' => $this->getRandomId('products', 'product_combination_id'),
            ]);
        }
        if ($request->productOption !== null) $this->saveProductOption($request, $product);
        if ($request->savedSubscriptionArray !== null) app(ProductSubscriptionController::class)->saveProductSubscription($request, $product);
        app(ProductBadgeController::class)->saveProductBadge($product);
        app(ApplicationController::class)->saveProductSaleChannel($request, $product);
        app(ProductCourseController::class)->saveCourse($request->input('details.curriculum'), $product);

        return response()->json(['status' => "success"]);
    }

    public function saveProductOption($request, $product)
    {
        // dd($request);
        // UsersProduct::find($product->id)->product_option()->detach($request->deletedSharedInputOption);
        // UsersProduct::find($product->id)->product_option()->detach($request->deletedInputOptionsId);
        UsersProduct::find($product->id)->product_option()->detach();
        if (count($request->deletedInputOptionsId) > 0) productOptionValue::whereIn('product_option_id', $request->deletedInputOptionsId)->delete();
        else productOptionValue::whereIn('id', $request->deletedInputId)->delete();
        productOption::whereIn('id', $request->deletedInputOptionsId)->delete();
        foreach ($request->productOption as $key => $data) {
            isset($data['id']) === false ? $data['id'] = 0 : $data['id'];
            $productOption = productOption::updateOrCreate(
                ['id' => $data['id']],
                [
                    'account_id' => $this->getCurrentAccountId(),
                    'display_name' => $data['display_name'],
                    'type' => $data['type'],
                    'help_text' => $data['help_text'],
                    'tool_tips' => $data['tool_tips'],
                    'is_range' => $data['is_range'],
                    'up_to' => $data['up_to'],
                    'at_least' => $data['at_least'],
                    'is_required' => $data['is_required'],
                    'is_shared' => $data['is_shared'],
                    'sort_order' => $key,
                    'is_total_Charge' => $data['is_total_Charge'],
                    'total_charge_amount' => $data['total_charge_amount'],
                ]
            );
            DB::table('product_option_users_product')->updateOrInsert(
                [
                    'users_product_id' => $product->id,
                    'product_option_id' => $productOption->id
                ]
            );
            foreach ($data['inputs'] as $key => $data) {
                isset($data['id']) === false ? $data['id'] = 0 : $data['id'];
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
        }
    }


    public function findVariantValueId($product_id, $variant_name)
    {
        // $variant = VariantValue::where('product_id', $product_id)->where('variant_value', $variant_name)->first();
        // return $variant !== null ? $variant->id : null;

        $variant_value = UsersProduct::find($product_id)->variant_values->where('variant_value', $variant_name)->first();
        return $variant_value !== null ? $variant_value->id : null;
    }

    public function getProduct($reference_key)
    {
        $type = "new";
        $product = (object)[];
        $productSales = (object)[];
        $variantArray = array();
        $variantNameArray = (object)[];
        $variantCombination = (object)[];
        $productOptionArray = [];
        $productCategory = array();
        $productCourse = array();
        $productCourseStudentIds = array();
        $paginatedCourseStudent = array();
        $productAccessListIds = array();
        $paginatedAccessList = array();
        $productSubscription = [];
        $currentAccountId = $this->getCurrentAccountId();
        $allCategories = $this->getAllCategoriesOrderByPriority($currentAccountId);
        $currency = Account::find($currentAccountId)->currency;
        $exceptDefault = Currency::where('account_id', $this->getCurrentAccountId())
            ->where('isDefault', '0')
            ->where('exchangeRate', '!=', null)
            ->get();
        $taxSetting = Tax::where('account_id', $currentAccountId)->first();
        $imageGallery = image_gallery::where('account_id', $currentAccountId)->get();
        if ($reference_key !== 'create') {
            $product = UsersProduct::with('modules')->where('account_id', $currentAccountId)->where('reference_key', $reference_key)->first();
            $type = $product->id;
            $productCourse = new ProductCourseResource($product);
            $productCategory = $product->categories;
            foreach ($product->product_variants as $tempVariant) {
                $variant = Variant::find($tempVariant->variant_id);
                $obj = (object)[
                    'id' => $variant->id,
                    'name' => $variant->variant_name,
                    'display_name' => $variant->display_name,
                    'is_shared' => $variant->is_shared,
                    'type' => $variant->type,
                    'errorMessage' => "",
                    'valueInput' => "",
                    'valueArray' => $variant->values
                ];
                array_push($variantArray, $obj);
            }
            foreach ($product->variant_details as $variant_details) {
                $key = $this->variantCombinationName($variant_details);
                $variantCombination->$key = (object)[
                    'ref' => $variant_details->reference_key,
                    'image_url' => $variant_details->image_url,
                    'title' => '',
                    'sku' => $variant_details->sku,
                    'price' => $variant_details->price,
                    'compared_at_price' => $variant_details->comparePrice,
                    'weight' => $variant_details->weight,
                    'quantity' => $variant_details->quantity,
                    'is_selling' => (bool)$variant_details->is_selling,
                    'is_visible' => (bool)$variant_details->is_visible,
                ];
                $variantNameArray->$key = (object)[
                    'option_1' => $this->findVariantName(1, $variant_details->option_1_id),
                    'option_2' => $this->findVariantName(1, $variant_details->option_2_id),
                    'option_3' => $this->findVariantName(1, $variant_details->option_3_id),
                    'option_4' => $this->findVariantName(1, $variant_details->option_4_id),
                    'option_5' => $this->findVariantName(1, $variant_details->option_5_id)
                ];
            }

            $allOrderDetails = OrderDetail::where('users_product_id', $product->id)->get();
            $productSales = (object)[
                'count' => $allOrderDetails->pluck('quantity')->sum(),
                'total' => $allOrderDetails->pluck('total')->sum()
            ];
            $productOptions = UsersProduct::find($product->id)->product_option;
            foreach ($productOptions as $productOption) {
                $productArray = count(DB::table('product_option_users_product')->where('product_option_id', $productOption->id)->get());
                $productOptionValue = productOptionValue::where('product_option_id', $productOption->id)->get();
                $productOption = [
                    "id" => $productOption->id,
                    "name" => $productOption->name,
                    "display_name" => $productOption->display_name,
                    "type" => $productOption->type,
                    "help_text" => $productOption->help_text,
                    "tool_tips" => $productOption->tool_tips,
                    "is_total_Charge" => $productOption->is_total_Charge,
                    "priority" => $productOption->global_priority,
                    "total_charge_amount" => $productOption->total_charge_amount,
                    "global_priority" => $productOption->global_priority,
                    "is_range" => $productOption->is_range,
                    "up_to" => $productOption->up_to,
                    "at_least" => $productOption->at_least,
                    "is_shared" => $productOption->is_shared,
                    "is_required" => $productOption->is_required,
                    "sort_order" => $productOption->sort_order,
                    'noOfProduct' => $productArray,
                    'inputs' => $productOptionValue,
                ];
                array_push($productOptionArray, $productOption);
            }
            //subscription
            $productSubscription = $product->subscription ?? [];

            // course student
            $courseService = new ProductCourseService($product);
            $paginatedCourseStudent = $courseService->getPaginatedCourseStudent();
            $productCourseStudentIds = $courseService->getAllStudentContactIds();

            // access list
            $accessService = new ProductAccessService($product->id);
            $paginatedAccessList = $accessService->getPaginatedAccessList();
            $productAccessListIds = $accessService->getAllAccessContactIds();
        }

        // Get All Shared Variant
        $allSharedVariants = Variant::where([
            ['account_id', $this->getCurrentAccountId()],
            ['is_shared', 1],
        ])->orderBy('global_priority')->get();
        $values = array();

        if (count($allSharedVariants) !== 0) {
            foreach ($allSharedVariants as $sharedVariant) {
                $count = ProductVariant::all()->where('variant_id', $sharedVariant->id)->count();
                $obj = (object)[
                    'id' => $sharedVariant->id,
                    'count' => $count,
                    'variant_name' => $sharedVariant->variant_name,
                    'type' => $sharedVariant->type,
                    'values' => $sharedVariant->values,
                ];
                array_push($values, $obj);
            }
        }

        $values = json_encode($values);
        $selectedSaleChannels = [];
        if (count((array)$product) > 0) {
            foreach ($product->saleChannels()->get() as $saleChannel) {
                $selectedSaleChannels[] = $saleChannel->type;
            };
            if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
        }
        $productSaleChannels = $selectedSaleChannels;
        $appSaleChannel = new SaleChannel();
        $activeSaleChannels = $appSaleChannel->getActiveSaleChannels($currentAccountId);
        $paginatedContacts = (new ProcessedContactService)->getPaginatedContacts();
        if (!str_contains(url()->current(), 'onboarding'))
            return Inertia::render('product/pages/AddProductPage', compact(
                'currency',
                'product',
                'type',
                'imageGallery',
                'variantArray',
                'variantCombination',
                'variantNameArray',
                'productSales',
                'productSaleChannels',
                'productOptionArray',
                'exceptDefault',
                'taxSetting',
                'allSharedVariants',
                'allCategories',
                'productCategory',
                'productSubscription',
                'activeSaleChannels',
                'productCourse',
                'paginatedCourseStudent',
                'paginatedContacts',
                'productCourseStudentIds',
                'paginatedAccessList',
                'productAccessListIds',
            ));
        return Inertia::render('onboarding/pages/Product', compact('currency',));
    }

    public function variantCombinationName($variant)
    {
        $option_1_name = $this->findVariantName(1, $variant->option_1_id);
        $option_2_name = $this->findVariantName(2, $variant->option_2_id);
        $option_3_name = $this->findVariantName(3, $variant->option_3_id);
        $option_4_name = $this->findVariantName(4, $variant->option_4_id);
        $option_5_name = $this->findVariantName(5, $variant->option_5_id);
        return trim($option_1_name . $option_2_name . $option_3_name . $option_4_name . $option_5_name);
    }

    public function findVariantName($index, $id)
    {
        $variant = VariantValue::find($id);
        if ($variant) {
            return $index == 1 ? $variant->variant_value : " / " . $variant->variant_value;
        }
        return "";
    }

    public function getProductVariant(Request $request)
    {
        $product = UsersProduct::where('account_id', $request->account_id)->find($request->id);
        $variantOptionArray = array();
        $variationCombination = (object)[];
        if ($product) {
            foreach ($product->variants as $variant) {
                $obj = (object)[
                    'name' => $variant->variant_name,
                    'valueArray' => $variant->values->pluck('variant_value')
                ];
                array_push($variantOptionArray, $obj);
            }
            foreach ($product->variant_details as $variant_details) {
                $key = $this->variantCombinationName($variant_details);
                $variationCombination->$key = (object)[
                    'sku' => $variant_details->sku,
                    'price' => $variant_details->price,
                    'compared_at_price' => $variant_details->comparePrice,
                    'weight' => $variant_details->weight,
                    'quantity' => $variant_details->quantity,
                    'is_visible' => $variant_details->is_visible,
                    'image_url' => $variant_details->image_url,
                ];
            }
        }
        return response()->json([
            'product' => $product,
            'variants' => $variantOptionArray,
            'combinationVariation' => $variationCombination
        ]);
    }

    public function changeProductStatus($id, Request $request)
    {
        $product = UsersProduct::find($id);
        $product->update(['status' => $request->status]);
        return response()->json(['status' => 'Status Changed.']);
    }

    public function deleteProduct(request $Request)
    {
        $orderSubscriptions = [];
        $currentAccountId = $this->getCurrentAccountId();
        $id = $Request->id;
        $deleteProduct = UsersProduct::where('account_id', $currentAccountId)->where('id', $id)->first();
        foreach ($deleteProduct->subscription as $subscription) {
            foreach ($subscription->orderSubscriptions as $orderSubscription) {
                array_push($orderSubscriptions, $orderSubscription);
            }
        }
        $filteredSubscription = collect($orderSubscriptions)->filter(function ($value, $key) {
            return $value['status'] !== 'canceled';
        });
        if (count($filteredSubscription) === 0) {
            $deleteProductVariants = $deleteProduct->product_variants;
            $deleteVariant = $deleteProduct->variants->where('is_shared', 0);
            $deleteCategory = ProductCategory::where('product_id', $id)->get();

            if ($deleteCategory) {
                foreach ($deleteCategory as $deleted) {
                    $deleted->delete();
                }
            }

            if (count($deleteProductVariants) !== 0) {
                foreach ($deleteProductVariants as $del) {
                    $del->delete();
                }
            }

            $deleteProduct->variant_details()->delete();
            $deleteProduct->delete();

            if (count($deleteVariant) !== 0) {
                foreach ($deleteVariant as $variant) {
                    $variant->values()->delete();
                    $variant->delete();
                }
            }
            return response()->json(
                $deleteProduct
            );
        }
        return response()->json('fail');
    }

    public function updateSelectedProduct(request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $usersProduct = UsersProduct::where('account_id', $currentAccountId)->where('id', $request->productId)->first();
        return response()->json($usersProduct);
    }

    public function getProductInventory()
    {
        $productArray = [];
        $products = UsersProduct::where('account_id', $this->getCurrentAccountId())
            ->orderBy('created_at', 'DESC')
            ->get();
        foreach ($products as $product) {
            if ($product->hasVariant) {
                foreach ($product->variant_details as $variant_detail) {
                    $key = $this->variantCombinationName($variant_detail);
                    $obj = (object)[
                        'product_id' => $product->id,
                        'variant_id' => $variant_detail->id,
                        'variant' => $key,
                        'product_image' => $variant_detail->image_url,
                        'product_title' => $product->productTitle,
                        'SKU' => $variant_detail->sku ?? 'No SKU',
                        'continue_sold' => $variant_detail->is_selling ? 'Continue Selling' : 'Stop Selling',
                        'available_stock' => $variant_detail->quantity,
                        'modify_quantity' => 0,
                        'type' => 'Add',
                    ];
                    array_push($productArray, $obj);
                }
            } else {
                $obj = (object)[
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'variant' => 'No Variant',
                    'product_image' => $product->productImagePath,
                    'product_title' => $product->productTitle,
                    'SKU' => $product->SKU ?? 'No SKU',
                    'continue_sold' => $product->is_selling ? 'Continue Selling' : 'Stop Selling',
                    'available_stock' => $product->quantity,
                    'modify_quantity' => 0,
                    'type' => 'Add',
                ];
                array_push($productArray, $obj);
            }
        }
        return Inertia::render('product/pages/ProductInventoryPage', compact('productArray'));
    }

    public function saveProductInventory(request $request)
    {
        $inventory = $request->modifyItem;
        $quantity = $inventory['type'] === 'Add'
            ? $inventory['modify_quantity'] + $inventory['available_stock']
            : $inventory['modify_quantity'];
        $product = $inventory['variant_id'] !== null
            ? VariantDetails::find($inventory['variant_id'])
            : UsersProduct::find($inventory['product_id']);
        $product->quantity = $quantity;
        $product->save();
        return response()->json($product->quantity);
    }

    public function getAllProductVariant()
    {
        $productArray = [];
        $accountId = $this->getCurrentAccountId();
        $allProduct = UsersProduct::where('account_id', $accountId)->get();
        foreach ($allProduct as $product) {
            $variationCombination = [];
            if ($product) {
                foreach ($product->variant_details as $variant_details) {
                    $tempArr = [];
                    $tempArr['key'] = $this->variantCombinationName($variant_details);
                    $tempArr['combination_id'] = $this->variantCombinationId($variant_details);
                    $tempArr['value'] = (object)[
                        'sku' => $variant_details->sku,
                        'price' => $variant_details->price,
                        'compared_at_price' => $variant_details->comparePrice,
                        'weight' => $variant_details->weight,
                        'quantity' => $variant_details->quantity,
                        'is_visible' => $variant_details->is_visible,
                        'image_url' => $variant_details->image_url,
                        'isChecked' => false,
                        'isDisabled' => false,
                    ];
                    array_push($variationCombination, $tempArr);
                }
            }
            $product->isCheckedAll = false;
            $product->isDisabledAll = false;
            $product->indeterminate = false;
            $usersProducts['product'] = $product->makeHidden(['variant_details']);
            $usersProducts['combinationVariation'] = $variationCombination;
            array_push($productArray, $usersProducts);
        }
        return response($productArray);
    }

    public function variantCombinationId($variant)
    {
        $combinationId = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($variant['option_' . $i . '_id'] != null) {
                array_push($combinationId, $variant['option_' . $i . '_id']);
            }
        }
        return $combinationId;
    }

    public function getAllCategories()
    {
        $accountId = $this->getCurrentAccountId();
        $allCategory = Category::where('account_id', $accountId)->with('products')->get();
        foreach ($allCategory as $category) {
            $category->isChecked = false;
            $category->isDisabled = false;
        }
        return response($allCategory);
    }

    public function getAllCategoriesWithActiveProducts($orders)
    {
        $accountId = $this->getCurrentAccountId();
        $allCategory = Category::where('account_id', $accountId)->with(array('products' => function ($query) {
            $query->where('status', 'active');
        }))->get();
        foreach ($allCategory as $category) {
            $category->products->map(function ($product) use ($orders) {
                $product->saleChannels = $this->selectedSaleChannels($product);
                $product->saleQuantity = $this->productSaleQuantity($product->id, $orders);
                return $product;
            });
        }
        return $allCategory;
    }

    public function getAllActiveProducts()
    {
        $currentAccountId = $this->getCurrentAccountId();
        $orders = DB::table('orders')->where('account_id', $currentAccountId)->where(function ($query) {
            $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
        })->get();
        $products = UsersProduct::where(['account_id' => $currentAccountId, 'status' => 'active'])->get()->map(function ($product) use ($currentAccountId, $orders) {
            $product->productComparePrice = $this->priceFormater($product->productComparePrice);
            $product->productPrice = $this->priceFormater($product->productPrice, null, false);
            $product->variantPrice =  $this->priceFormater($this->variantPrice($product));
            $product->reviews = $this->reviews($currentAccountId, $product->id);
            $product->badgeLabel = $this->badgeLabel($currentAccountId, $product);
            $product->custom_options = $this->getProductOption($product);
            $product->saleQuantity = $this->productSaleQuantity($product->id, $orders);
            $product->saleChannels = $this->selectedSaleChannels($product);
            return $product;
        });
        $allCategories = $this->getAllCategoriesWithActiveProducts($orders);
        return response()->json([
            'products' => $products,
            'categories' => $allCategories,
        ]);
    }

    public function variantPrice($product)
    {
        if ($product->hasVariant) $variantPrice = $product->variant_details[0]->price;
        foreach ($product->variant_details as $variantDetail) {
            if ($variantPrice !== $variantDetail->price) {
                $product->variantPrice =
                    $variantPrice < $variantDetail->price
                    ? $variantPrice
                    : $variantDetail->price;
                $variantPrice = $product->variantPrice;
            }
        }
        return $product->variantPrice;
    }

    public function reviews($accountId, $productId)
    {
        $reviews = ProductReview::where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $productId])->get();
        $reviewSettings = ProductReviewSetting::where('account_id', $accountId)->first();
        $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
        $totalRate = 0;
        $avgRate = 0;
        foreach ($reviews as $review) {
            $totalRate += $review->star_rating;
            $review->productTitle = UsersProduct::where(['account_id' => $accountId, 'id' => $review->product_id])->first()->productTitle;
        }
        if ($totalRate !== 0) $avgRate = $totalRate / count($reviews);
        $starWidth = intval(($avgRate - intval($avgRate)) * 100);
        $reviews = [
            'displayReview' => $displayReview,
            'reviewSettings' => $reviewSettings,
            'avgRate' => $avgRate,
            'starWidth' => $starWidth,
            'reviewCount' => count($reviews),
        ];
        return $reviews;
    }

    public function getAllCategoriesOrderByPriority($accountId)
    {
        return Category::where('account_id', $accountId)->orderBy('priority')->get();
    }

    public function badgeLabel($accountId, $product)
    {
        $badgeLabel = Badges::where('account_id', $accountId)->orderBy('priority', 'asc')->first();
        if ($badgeLabel) {
            if ($badgeLabel->select_products !== 'all') {
                $badgeLabel = UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
            }
        }
        return $badgeLabel;
    }

    public function duplicateProduct(Request $request)
    {
        try {
            DB::beginTransaction();

            $product = UsersProduct::firstWhere('reference_key', $request->refKey);

            $newProduct = $product->replicate();
            $index = -1;
            $suffix = '';
            do {
                $index++;
                $suffix = $index === 0 ? '-copy' : "-copy-$index";
                $isExisted =  UsersProduct::where('path', $newProduct->path . $suffix)->exists();
            } while ($isExisted);
            $newProduct->title .= ' - Copy' . ($index > 0 ? " - $index" : "");
            $newProduct->path .= $suffix;
            $newProduct->meta_title .= $suffix;
            $newProduct->reference_key = $this->generateReferenceKey();
            $newProduct->product_combination_id = $this->getRandomId('products', 'product_combination_id');

            $newProduct->save();

            $product->modules->each(function ($module) use ($newProduct) {
                $newModule = $module->replicate();
                $newModule->product_id = $newProduct->id;
                $newModule->save();
                $module->lessons->each(function ($lesson) use ($newModule) {
                    $newLesson = $lesson->replicate();
                    $newLesson->product_module_id = $newModule->id;
                    $newLesson->save();
                });
            });


            $variantValueIdMap = [];
            $product->variants->each(function ($variant) use ($product, $newProduct, &$variantValueIdMap) {
                $newVariant = $variant->replicate();
                $newVariant->save();

                $productVariant = $product->product_variants()->firstWhere('variant_id', $variant->id);
                $newProductVariant = $productVariant->replicate();
                $newProductVariant->product_id = $newProduct->id;
                $newProductVariant->variant_id = $newVariant->id;
                $newProductVariant->save();

                $variant->values->each(function ($value) use ($newVariant, &$variantValueIdMap) {
                    $newVariantValue = $value->replicate();
                    $newVariantValue->variant_id = $newVariant->id;
                    $newVariantValue->save();
                    $variantValueIdMap[$value->id] = $newVariantValue->id;
                });
            });
            $product->variant_details->each(function ($detail) use ($newProduct, $variantValueIdMap) {
                $newVariantDetail = $detail->replicate();
                $newVariantDetail->product_id = $newProduct->id;
                $newVariantDetail->reference_key = $this->getRandomId('products', 'reference_key');
                for ($i = 1; $i <= 5; $i++) {
                    if ($detail->{'option_' . $i . '_id'}) {
                        $newVariantDetail->{'option_' . $i . '_id'} = $variantValueIdMap[$detail->{'option_' . $i . '_id'}];
                    }
                }
                $newVariantDetail->save();
            });

            $product->product_option->each(function ($option) use ($newProduct) {
                DB::table('product_option_users_product')->updateOrInsert(
                    [
                        'users_product_id' => $newProduct->id,
                        'product_option_id' => $option->id
                    ]
                );
            });

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \RuntimeException('Failed to duplicate product');
        }
    }
}
