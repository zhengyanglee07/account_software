<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\ProductCategory;
use Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function current_accountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    public function getProductCategoryPage() {
        $currentAccountId = $this->current_accountId();
        $categories = Category::where('account_id', $currentAccountId)->orderBy('priority')->get()->map(function($category){
            $category->count=ProductCategory::where('category_id', $category->id)->count();
            $path = Str::lower(Str::squish(preg_replace('/[^\w\s]+/u', "", $category->name)));
            $category->path = Str::replace(' ', '-',  $path );
            return $category;
        });
        return Inertia::render('product/pages/ProductCategories', compact('categories'));
    }

    public function addCategory(Request $request) {
        $currentAccountId = $this->current_accountId();
        $checkExisting = Category::where([
            ['account_id', $currentAccountId],
            ['name', $request->input('name')]
        ])->first();

        if($checkExisting !== null) {
            return response()->json(['status' => 'Error : Category Exist!']);
        }
        $latestCategory = Category::where('account_id', $currentAccountId)->orderByDesc('priority')->first();
        if($latestCategory) $priorityCount = $latestCategory->priority + 1;
        $path = Str::lower(Str::squish(preg_replace('/[^\w\s]+/u', "", $request->input('name'))));
        $categoryPath = Str::replace(' ', '-',  $path );
        $category = Category::create([
            'account_id' => $currentAccountId,
            'name' => $request->input('name'),
            'priority' => $priorityCount ?? null,
            'path' => $categoryPath,
        ]);

        $categories = Category::where('account_id', $currentAccountId)->orderBy('priority')->get();

        return response()->json(['status' => 'Success', 'categories' => $categories,'category'=> $category]);
    }

    public function updateCategory($id, Request $request) {
        $currentAccountId = $this->current_accountId();
        $category = Category::find($id);

        if($category === null) {
            return response()->json(['status' => 'Error : Something Went Wrong!']);
        }

        $checkExisting = Category::where([
            ['account_id', $currentAccountId],
            ['name', $request->input('name')]
        ])->first();

        if($checkExisting !== null) {
            return response()->json(['status' => 'Error : Category Exist!']);
        }

        $category->name = $request->input('name');
        $path = Str::lower(Str::squish(preg_replace('/[^\w\s]+/u', "", $request->input('name'))));
        $category->path  = Str::replace(' ', '-',  $path );
        $category->save();
        return response()->json(['status' => 'Success']);
    }

    public function deleteCategory($id) {
        $category = Category::find($id);
        if($category === null) {
            return response()->json(['status' => 'Error : Something went wrong!']);
        }
        $category->delete();
        return response()->json(['status' => 'Success']);
    }

    public function productsUpdateCategory($type, Request $request) {
        // dd($request);
        $category = Category::find($request->categoryId);
        if(!$category) {
            return response()->json(['status' => 'Error : Something went wrong!']);
        }
        $category->products()->detach($request->productIds);
        if($type==='add')$category->products()->attach($request->productIds);
        return response()->json(['status' => 'Success']);
    }

    public function updateCategoryPriority(Request $request) {
        // dd($request);
        foreach($request->items as $key=>$item){
            // dd($item);
            $category = Category::find($item['id']);
            if(!$category) {
                return response()->json(['status' => 'Error : Something went wrong!']);
            }
            $category->priority = $key;
            $category->save();
        }

        $currentAccountId = $this->current_accountId();
        $categories = Category::where('account_id', $currentAccountId)->orderBy('priority')->get();
        $categoryArray = array();
        $count = 0;
        foreach ($categories as $category) {
            $count = ProductCategory::where('category_id', $category->id)->count();
            $obj = (object)[
                'id' => $category->id,
                'name' => $category->name,
                'count' => $count,
            ];
            array_push($categoryArray, $obj);
        }

        return response()->json(['status' => 'Success', 'items' => $categoryArray ]);
    }
}
