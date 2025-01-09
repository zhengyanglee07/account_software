<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\ProductRecommendation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductRecommendationController extends Controller
{
    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function productRecommendations($account)
    {
        $selectedProductRecommendations = $account->productRecommendationsTypes();
        return ProductRecommendation::select('type')->get()->map(function ($recommendation, $index) use ($selectedProductRecommendations) {
            $selectedRecommendation = $selectedProductRecommendations->firstWhere('type', $recommendation->type);
            $latestPriority = $selectedProductRecommendations->isNotEmpty() ? $selectedProductRecommendations->last()->priority : $index;
            $recommendation->status =  $selectedRecommendation ? 'active' : 'inactive';
            $recommendation->priority = $selectedRecommendation ? $selectedRecommendation->priority :  $latestPriority + $index + 1;
            return $recommendation;
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $account = Account::find($this->accountId());
        $productRecommendations = $this->productRecommendations($account);
        return Inertia::render('product-recommendation/pages/ProductRecommendationDashboard', compact('productRecommendations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $selectedTypes = $request->selectedTypes;
        $account = Account::find($this->accountId());
        $selectedTypeIds = [];
        foreach ($selectedTypes as $type) {
            $productRecommendation = ProductRecommendation::firstWhere('type', $type);
            if ($productRecommendation) $selectedTypeIds[] = $productRecommendation->id;
        }
        $syncData = [];
        foreach ($selectedTypeIds as $key => $id) {
            $syncData[$id] = [
                'priority' => $key
            ];
        };
        $account->productRecommendations()->sync($syncData);
        $productRecommendations = $this->productRecommendations($account);
        return response()->json($productRecommendations);
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
     * @param  \App\ProductRecommendation  $productRecommendation
     * @return \Illuminate\Http\Response
     */
    public function show(ProductRecommendation $productRecommendation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductRecommendation  $productRecommendation
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductRecommendation $productRecommendation)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductRecommendation  $productRecommendation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductRecommendation $productRecommendation)
    {
        //
    }
}
