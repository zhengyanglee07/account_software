<?php

namespace App\Http\Controllers;

use App\TaxCountry;
use Illuminate\Http\Request;
use Auth;
use App\Account;
use App\TaxCountryRegion;
use App\Tax;
use App\Traits\AuthAccountTrait;
use Hashids\Hashids;
use Inertia\Inertia;

class TaxController extends Controller
{
    use AuthAccountTrait;

    public function currentAccount(){
        $user = Auth::user();
        $account = Account::where('id',$user->currentAccountId)->first();
        return $account;

    }

    public function showTaxSettings(){
        $hashids = new Hashids('', 10);
        $account = $this->currentAccount();
        $taxCountry = [];
        $taxSetting = Tax::where('account_id',$account->id)->first();
        // dd($taxSetting!==null);
        if($taxSetting !== null){
            $taxCountry = TaxCountry::where('tax_setting_id',$taxSetting->id)->get();
            foreach ($taxCountry as $country){
                $country->urlId = $hashids->encode($country->id);
            }
        }

        return Inertia::render('setting/pages/TaxSettings', compact('taxSetting','taxCountry'));
    }

    public function addNewTax($urlId){

        $account = $this->currentAccount();
        $type="new";
        $taxCountry=[];
        $taxCountryRegion =[];
        $otherTaxCountry = $account->taxCountry;

        if($urlId !== 'new'){
            $type="edit";
            $hashids = new Hashids('',10);
            $idArr = $hashids->decode($urlId);
            $id = $idArr[0];
            $taxCountry = $account->taxCountry->where('id',$id)->first();
            $taxCountryRegion = TaxCountryRegion::where('country_id',$id)->get();

        }

        return Inertia::render('setting/pages/AddNewTax', compact('type','taxCountry','taxCountryRegion','otherTaxCountry'));
    }

    public function fetchCountryAndRegion(){


        $account= $this->currentAccount();

        $existedRegion = $account->taxCountry;

        $path = storage_path() . "/json/data.json";
        $API = json_decode(file_get_contents($path), true);



        return response()->json(['API'=>$API,'existedRegion'=>$existedRegion]);
        // return $API;

    }

    public function saveTaxCountrySetting(Request $request){
        // Validator::make($request, [
        //     'taxName' => ['required'],
        //     'selectedCountry' => ['required'],
        //     'countryTax' => ['required','numeric'],
        //     'zip' => 'required',
        //     'region' => 'required',
        //     'country' =>'required',
        //     'phoneNo' => 'required'
        // ],[
        //     'required'=>'Field is required'
        // ])->validate();

        $currentSelected = array_map(function($data){
            return $data['id'];
        },$request->selectedSubRegion);
        $existedRegion = array_map(function($data){
            return $data['id'];
        },$request->taxCountryRegion);
        $removedRegionId = array_diff($existedRegion,$currentSelected);

        if($removedRegionId!=[]){
            TaxCountryRegion::whereIn('id',$removedRegionId)->delete();
        }

        $account = $this->currentAccount();
        $taxSetting = Tax::firstOrNew(['account_id'=>$account->id]);
        $taxSetting->save();

        if($request->type == 'new'){
            $taxCountry = new TaxCountry();
            $taxCountry->country_name = $request->selectedCountry;
            $taxCountry->tax_setting_id = $taxSetting->id;
        }else{
            $taxCountry = TaxCountry::where('country_name',$request->selectedCountry)->where('tax_setting_id',$taxSetting->id)->first();
        }
            $taxCountry->tax_name = $request->taxName;
            $taxCountry->country_tax = $request->countryTax;
            $taxCountry->save();

        if($request->selectedSubRegion !== []){
            foreach($request->selectedSubRegion as $subRegion){
                $taxCountryRegion = TaxCountryRegion::firstOrNew(['country_id'=>$taxCountry->id,'id'=>$subRegion['id']]);
                $taxCountryRegion->sub_region_name = $subRegion['stateName'];
                $taxCountryRegion->tax_name = $subRegion['stateTaxName'];
                $taxCountryRegion->tax_rate = $subRegion['stateTaxRate'];
                $taxCountryRegion->tax_calculation = $subRegion['taxCalculation'];
                $taxCountryRegion->total_tax = $subRegion['totalTax'];
                $taxCountryRegion->save();
            }
        }

        $totalTaxRegion = TaxCountryRegion::where('country_id',$taxCountry->id)->count();
        $taxCountry->has_sub_region =($totalTaxRegion > 0) ? true:false;
        $taxCountry->save();

    }

    public function deleteTaxRegion(Request $request){
        $account= $this->currentAccount();
        $taxCountry = $account->taxCountry->where('id',$request->id)->first();
        $taxCountry->delete();

    }

    public function saveTaxSetting(Request $request){
        // dd($request);
        $account = $this->currentAccount();
        Tax::updateOrCreate(
            ['account_id' =>  $account->id],
            ['is_product_include_tax' => $request->isProductIncludeTax,
             'is_shipping_fee_taxable' => $request->isTaxIncludeShipping]
        );
    }

    public function getTaxSetting(Request $request)
    {
        $accountId = $this->getCurrentAccountId() ;
        $taxName = '';
        $taxRate = 0;
        $taxSetting = Tax::where('account_id', $accountId)->first();
        // dump($request);
        if ($taxSetting !== null) {
            $taxSettingCountry = TaxCountry::where('tax_setting_id', $taxSetting->id)->where('country_name', $request->taxCountry)->first();
            if ($taxSettingCountry !== null) {
                if ($taxSettingCountry->has_sub_region) {
                    $taxSettingState = TaxCountryRegion::where('country_id', $taxSettingCountry->id)->where('sub_region_name', $request->taxState)->first();
                    if ($taxSettingState !== null) {
                        $taxRate = $taxSettingState->total_tax;
                        $taxName = $taxSettingState->tax_name;
                    } else {
                        $taxRate = $taxSettingCountry->country_tax;
                        $taxName = $taxSettingCountry->tax_name;
                    }
                } else {
                    $taxRate = $taxSettingCountry->country_tax;
                    $taxName = $taxSettingCountry->tax_name;
                }
            }
        }
        return response()->json(['taxRate' => $taxRate, 'taxName' => $taxName, 'setting' => $taxSetting]);
    }
}
