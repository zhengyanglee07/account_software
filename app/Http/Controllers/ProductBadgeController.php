<?php

namespace App\Http\Controllers;

use Auth;
use App\Badges;
use App\Category;
use App\Currency;
use Hashids\Hashids;
use App\UsersProduct;
use App\ProductCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProductBadgeController extends Controller
{
    //
    public function accountId(){
        return Auth::user()->currentAccountId;
    }

    public function getProductBadges(){
        $hashids = new Hashids('', 10);
        $badges = Badges::where('account_id', $this->accountId())->orderBy('priority', 'asc')->get();
        foreach($badges as $badge){
            $productTitle = '';
            $badge->urlId = $hashids->encode($badge->id);
            $selectedProducts = Badges::find($badge->id)->products()->where('type', null)->get();
            if($badge->select_products==='specific'){
                $productTitle .= 'No select any products...';
                foreach($selectedProducts as $key=>$selectedProduct){
                    if($key===0) {$productTitle .= $selectedProduct->productTitle;}
                    else{$productTitle .= ', ' . $selectedProduct->productTitle;}
                }
            }
            if($badge->select_products==='category'){
                $selectedCategories = json_decode($badge->selected_categories) ?? [];
                foreach($selectedCategories as $key=>$id){
                    $name = Category::where(['id'=> $id, 'account_id'=> $this->accountId(),])->first()->name;
                    if($key===0) {  $productTitle .= 'All Products under ' . $name;}
                    else{ $productTitle .= ', ' . $name;}
                }
                if(count(($selectedCategories))>0) $productTitle .= count(($selectedCategories))>1 ? ' categories' : ' category';
            }
            $badge->selectedProduct = $productTitle;
        };
        return Inertia::render('product/pages/ProductLabelDashboard',compact(['badges']));
    }

    public function addProductBadgePage($urlId){
        $id = $urlId;
        if($id!=='add'){
            $hashids = new Hashids('',10);
            $idArr = $hashids->decode($urlId);
            $id =  $idArr ? $idArr[0] : '';
        }
        $badge = Badges::where('id', $id)->where('account_id', $this->accountId())->first();
        $selectedId=[];
        if($badge) {
            $selectedProducts = Badges::find($badge->id)->products()->where('type', null)->get();
            foreach($selectedProducts as $selectedProduct){
                $selectedId[]=$selectedProduct->id;
            }
            $badge->selected_product = $selectedId;
            $badge->selected_categories = json_decode($badge->selected_categories);
        }
        $allProducts = UsersProduct::where('account_id', $this->accountId())->get();
        $allCategories = Category::where('account_id', $this->accountId())->get()->map(function($category){
            $category->count = count(ProductCategory::where('category_id', $category->id)->get());
            return $category;
        });
        $currencyDetails = Currency::where(['account_id'=>$this->accountId(), 'isDeFault'=>'1'])->first();
        return Inertia::render('product/pages/AddProductLabelPage', compact(['badge', 'allProducts', 'currencyDetails', 'allCategories']));
    }

    public function addProductBadge(Request $request){

        $badges = Badges::where('account_id', $this->accountId())->orderBy('priority', 'desc')->get();
        $priority = count($badges)>0 ?  $badges[0]->priority+1 : count($badges)+1;
        $newBadge = Badges::create([
            'account_id' => $this->accountId(),
            'badge_name' => $request->badgeName,
            'badge_design'=> $request->badgeDesign,
            'text'=> $request->text,
            'font_size'=> $request->fontSize,
            'text_color'=> $request->textColor,
            'font_family'=> $request->fontFamily,
            'background_color'=> $request->backgroundColor,
            'position'=> $request->position,
            'select_products'=> $request->productSelect,
            'selected_categories'=> json_encode($request->selectedCategory),
            'margin_size'=> $request->marginSize,
            'priority'=> $priority,
        ]);
        if( $request->productSelect==='specific') {
            $newBadge->products()->sync($request->selectedProduct);
        }
        if( $request->productSelect==='all') {
            $allProducts = UsersProduct::where('account_id', $this->accountId())->get();
            foreach($allProducts as $product){
                $newBadge->products()->attach($product->id, ['type' => 'all']);
            }
        }
        if( $request->productSelect==='category') {
            $selected = [];
            foreach($request->selectedCategory as $category){
                $products = ProductCategory::where('category_id', $category)->get();
                foreach($products as $product){
                    if(!in_array($product->product_id, $selected)) $selected[]=$product->product_id;
                }
            }
            foreach($selected as $id){
                $newBadge->products()->attach($id, ['type' => 'category']);
            }

        }
        return response()->json(['message' => 'Successfully saved']);
    }

    public function editProductBadge(Request $request, $urlId){
        $badge = Badges::where('id', $urlId)->where('account_id', $this->accountId())->first();
        $badge->update([
            'badge_name' => $request->badgeName,
            'badge_design'=> $request->badgeDesign,
            'text'=> $request->text,
            'font_size'=> $request->fontSize,
            'text_color'=> $request->textColor,
            'font_family'=> $request->fontFamily,
            'background_color'=> $request->backgroundColor,
            'position'=> $request->position,
            'select_products'=> $request->productSelect,
            'selected_categories'=> json_encode($request->selectedCategory),
            'margin_size'=> $request->marginSize,
        ]);
        if( $request->productSelect==='specific') {
            $badge->products()->where('type', null)->detach();
            $badge->products()->attach($request->selectedProduct);
        }
        if( $request->productSelect==='all') {
            $allProducts = UsersProduct::where('account_id', $this->accountId())->get();
            foreach($allProducts as $product){
                $badge->products()->attach($product->id, ['type' => 'all']);
            }
        }
        if( $request->productSelect==='category') {
            $prevSelectedCategories = Badges::find($badge->id)->products()->where('type', 'category')->get();
            foreach($prevSelectedCategories as $prevSelected){
                $badge->products()->where('type', 'category')->detach($prevSelected->users_product_id);
            }
            $selected = [];
            foreach($request->selectedCategory as $category){
                $products = ProductCategory::where('category_id', $category)->get();
                foreach($products as $product){
                    if(!in_array($product->product_id, $selected)) $selected[]=$product->product_id;
                }
            }
            foreach($selected as $id){
                $badge->products()->attach($id, ['type' => 'category']);
            }

        }
        return response()->json(['message' => 'Successfully updated']);
    }

    public function deleteProductBadge($id){
        $badge = Badges::where([
            'id' => $id,
            'account_id' => $this->accountId(),
        ])->firstOrFail();
        $badge->products()->detach();
        $badge->delete();
        $hashids = new Hashids('', 10);
        $badges = Badges::where('account_id', $this->accountId())->orderBy('priority', 'asc')->get();
        foreach($badges as $badge){
            $productTitle = '';
            $badge->urlId = $hashids->encode($badge->id);
            $selectedProducts = Badges::find($badge->id)->products()->get();
            foreach($selectedProducts as $key=>$selectedProduct){
                if($key===0) {$productTitle .= $selectedProduct->productTitle;}
                else{$productTitle .= ', ' . $selectedProduct->productTitle;}
            }
            $badge->selectedProduct = $productTitle;
        };
        return response()->json(['message' => 'Successfully updated', 'badges' => $badges]);
    }

    public function editBadgePriority(Request $request){
        foreach($request->items as $key=>$badge){
            $badge = Badges::where('id', $badge['id'])->where('account_id', $this->accountId())->first();
            $badge->update([
                'priority'=> $key+1,
            ]);
        }
        $hashids = new Hashids('', 10);
        $badges = Badges::where('account_id', $this->accountId())->orderBy('priority', 'asc')->get();
        foreach($badges as $badge){
            $productTitle = '';
            $badge->urlId = $hashids->encode($badge->id);
            $selectedProducts = Badges::find($badge->id)->products()->get();
            if($badge->select_products==='specific'){
                foreach($selectedProducts as $key=>$selectedProduct){
                    if($key===0) {$productTitle .= $selectedProduct->productTitle;}
                    else{$productTitle .= ', ' . $selectedProduct->productTitle;}
                }
            }
            if($badge->select_products==='category'){
                $selectedCategories = json_decode($badge->selected_categories) ?? [];
                foreach($selectedCategories as $key=>$id){
                    $name = Category::where(['id'=> $id, 'account_id'=> $this->accountId(),])->first()->name;
                    if($key===0) {  $productTitle .= 'All Products under ' . $name;}
                    else{ $productTitle .= ', ' . $name;}
                }
                if(count(($selectedCategories))>0) $productTitle .= count(($selectedCategories))>1 ? ' categories' : ' category';
            }
            $badge->selectedProduct = $productTitle;
        };
        return response()->json(['message' => 'Successfully saved', 'items' => $badges]);
    }  
    
    public function saveProductBadge($product){
        $selectedCategory = [];
        foreach( $product->categories as $category){
            $selectedCategory[] = $category->id;
        }
        $badges = Badges::where('account_id', $this->accountId())->get()->map(function($badge){
            $badge->selected_categories = json_decode($badge->selected_categories);
            return $badge;
        });
        foreach($badges as $badge){
            if( $badge->select_products==='all') {
                $badge->products()->detach($product->id);
                $badge->products()->attach($product->id, ['type' => 'all']);
            }
            if( $badge->select_products==='category') {
                foreach($selectedCategory as $selected){
                    if(in_array($selected, $badge->selected_categories ?? []))
                        $badge->products()->detach($product->id);
                        $badge->products()->attach($product->id, ['type' => 'category']);
                }

            }
        }
        
    }


}
