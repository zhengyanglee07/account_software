<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\MiniStore;
use App\UsersProduct;
use App\AccountDomain;
use App\ProductSubscription;
use Illuminate\Http\Request;
use App\MiniStoreChecklistHeader;
use App\Http\Controllers\Controller;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class MiniStoreController extends Controller
{
    use PublishedPageTrait;

    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function getMiniStoreInfo()
    {
        $miniStore = MiniStore::firstwhere([
            'account_id' => $this->accountId(),
        ]);
        $account = Account::firstwhere([
            'id' => $this->accountId(),
        ]);
        $onboarding = false;
        if (str_contains(url()->current(), 'onboarding')) {
            $onboarding = true;
            return Inertia::render('onboarding/pages/MiniStoreSetup', compact(['miniStore', 'account', 'onboarding']));
        }

        return Inertia::render('mini-store/pages/MiniStorePage', compact(['miniStore', 'account', 'onboarding']));
    }

    public function getMiniStoreElements()
    {

        $miniStore = MiniStore::firstwhere([
            'account_id' => $this->accountId(),
        ]);

        return response()->json($miniStore);
    }

    public function editMiniStore(Request $request)
    {
        $account = Account::find($this->accountId());
        $miniStore = MiniStore::firstwhere([
            'account_id' => $this->accountId(),
        ]);

        if ($miniStore !== null) {
            $miniStore->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_enabled_delivery_checker' => $request->isEnabledDeliveryChecker,
            ]);
        } else {
            $miniStore = MiniStore::create([
                'account_id' => $this->accountId(),
                'name' => $request->name,
                'description' => $request->description,
                'image' => $account->company_logo,
                'is_enabled_delivery_checker' => $request->isEnabledDeliveryChecker,
            ]);
        }
        return response()->json($miniStore);
    }


    public function checkedlist($index)
    {
        switch ($index) {
            case 1:
                return [
                    'Setup ministore details',
                ];
                break;
            case 2:
                return [
                    'Add products',
                    'Setup ministore details',
                ];
                break;
            case 3:
                return [
                    'Add products',
                    'Setup ministore details',
                    'Setup shipping methods',
                ];
                break;
            case 4:
                return  [
                    'Add products',
                    'Setup ministore details',
                    'Setup shipping methods',
                    'Setup payment methods',
                ];
                break;
            case 5:
                return [
                    'Add products',
                    'Setup ministore details',
                    'Setup shipping methods',
                    'Setup payment methods',
                    'Test the ministore',
                ];
                break;
        }
    }

    public function updateMiniStoreChecklistHeader(Request $request)
    {

        $checkListHeader = MiniStoreChecklistHeader::firstwhere([
            'account_id' => $this->accountId(),
        ]);
        if ($checkListHeader) {
            $checkListHeader->update([
                'header_index' => $request->headerIndex,
                'checked_list' => $this->checkedlist($request->headerIndex),
                'is_show' => $request->headerIndex > 4 ? false : true,
            ]);
        } else {
            $checkListHeader = MiniStoreChecklistHeader::create([
                'header_index' => $request->headerIndex,
            ]);
        }
        return response()->json($checkListHeader);
    }

    public function getStoreType()
    {

        $storeType = AccountDomain::firstwhere([
            'account_id' => $this->accountId(),
        ])->type;

        return response()->json($storeType);
    }

    public function filterProducts(Request $request)
    {
        $allProducts = UsersProduct::where([
            'account_id' => $request->id,
        ])->get();
        foreach ($allProducts as $product) {
            $product['containSubscription'] = ProductSubscription::where('users_products_id', $product->id)->first() ? true : false;
        }
        $filteredProducts = $allProducts->filter(function ($value) {
            return $value['containSubscription'] == true;
        });
        $subbedProductsId = [];
        foreach ($filteredProducts as $product) {
            $subbedProductsId[] = $product->id;
        }

        return response()->json($subbedProductsId);
    }
}
