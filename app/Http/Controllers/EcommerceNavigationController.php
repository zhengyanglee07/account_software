<?php

namespace App\Http\Controllers;

use App\Account;
use App\Category;
use App\EcommerceNavBar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Page;
use App\LegalPolicyType;
use App\UsersProduct;
use Auth;
use Inertia\Inertia;

class EcommerceNavigationController extends Controller
{
    private function currentAccountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    public function index()
    {
        $dbNavigations = EcommerceNavBar::where([
            'account_id' => $this->currentAccountId(),
        ])->get();

        return Inertia::render('online-store/pages/EcommerceNavigation', compact(
            'dbNavigations'
        ));
    }

    public function destroy(EcommerceNavBar $nav)
    {
        $nav->delete();
    }

    public function createNewMenu(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        $newMenu = new EcommerceNavBar();
        $newMenu->menu_items = json_encode($request->newArray);
        $newMenu->account_id = $currentAccountId;
        $newMenu->title = $request->menuTitle;
        $newMenu->save();
    }

    public function getMenu($referenceKey)
    {
        $accountId = $this->currentAccountId();
        $accountRandomId = Account::findOrFail($accountId)->accountRandomId;
        $menu = (object)[];
        if ($referenceKey != 'create') {
            $menu = EcommerceNavBar::where([
                'account_id' => $accountId,
                'id' => $referenceKey
            ])->first();
        }
        $allProductsArray = UsersProduct::select('id', 'title', 'reference_key', 'path')->where([
            'account_id' => $accountId,
            'status' => 'active'
        ])->get();
        $allCategoriesArray = Category::where('account_id', $accountId)->get();
        $allPagesArray = Page::select('id', 'name', 'path', 'duplicated_count')->where([
            'account_id' => $accountId,
            'is_landing' => false,
        ])->get();
        $allLegalPolicy = LegalPolicyType::all();
        return Inertia::render('online-store/pages/EcommerceNavigationMenu', compact('menu', 'accountId', 'allPagesArray', 'allProductsArray', 'allLegalPolicy', 'allCategoriesArray'));
    }

    public function saveMenuList(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        EcommerceNavBar::where([
            'account_id' => $currentAccountId,
            'id' => $request->id
        ])->update([
            'title' => $request->inputTitle,
            'menu_items' => $request->menuItemArray
        ]);
        $menu = EcommerceNavBar::where([
            'account_id' => $currentAccountId,
            'id' => $request->id,
        ])->first();
        $menu['menu_items'] = json_decode($menu['menu_items']);
        return response()->json(['menu' => $menu]);
    }

    public function getAllMenu()
    {
        $currentAccountId = $this->currentAccountId();
        $allMenu = EcommerceNavBar::where([
            'account_id' => $currentAccountId,
        ])->get();
        return response()->json(['allMenu' => $allMenu]);
    }
}
