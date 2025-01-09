<?php

namespace App\Http\Controllers\API;

use App\UsersProduct;
use App\Category;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\UsersProductTrait;

class UserProductController extends Controller
{
    use UsersProductTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $accountId = $request->user()->currentAccountId;
        $products = UsersProduct::with(['variant_details', 'variant_values'])->where([
            'account_id' => $accountId,
            'status' => 'active',
        ])->latest()->get()->map(function ($product) {
            $product->categories = $product->categories()->without('products')->get();
            return $product;
        });
        $categories = Category::without('products')->where('account_id', $accountId)->select("id", "name")->get();

        $this->setVariantLabel($products);
        $this->setSoldQuantity($accountId, $products);
        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
