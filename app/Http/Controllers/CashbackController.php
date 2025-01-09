<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Segment;
use App\ProcessedContact;
use App\Cashback;
use App\PeopleCashback;
use App\Currency;
use App\SaleChannel;
use App\Traits\AuthAccountTrait;
use Inertia\Inertia;
class CashbackController extends Controller
{
    use AuthAccountTrait;

    public function generateReferenceKey()
    {
        $condition = true;
        $currentAccountId = $this->getCurrentAccountId();
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Cashback::where([
                ['account_id', $currentAccountId],
                ['ref_key', $randomId]
            ])->exists();
        } while ($condition);
        return $randomId;
    }

    public function getCashbackPage()
    {
        $currentAccountId = $this->getCurrentAccountId();
        $cashbacks = Cashback::where('account_id', $currentAccountId)->get();
        $segments = Segment::where('account_id', $currentAccountId)->get();
        $cashbackDetails = array();
        
        foreach ($cashbacks as $cashback) {
            $segmentName = '';
            if ($cashback->for_all == '1') {
                $segmentName = 'All';
            } else {
                $selectedSegments = $cashback->segments;
                foreach ($selectedSegments as $segment) {
                    $segmentName = $segmentName === '' 
                                        ? $segmentName.$segment->segmentName 
                                        : $segmentName.', '.$segment->segmentName;
                }
            }
            $cashbackDetails[$cashback->id] = $segmentName;
        }
        
        return Inertia::render('cashback/pages/CashbackPageDatatable', compact('segments', 'cashbacks', 'cashbackDetails'));
    }

    public function getCashbackSettingPage($refKey)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $cashback = Cashback::where('account_id', $currentAccountId)->where('ref_key', $refKey)->first();
        if(isset($cashback)){
            $cashback['salesChannel'] = $cashback->saleChannels()->get();
        }
        $cashbackTitles = Cashback::where('account_id', $currentAccountId)->pluck('cashback_title')->toArray();
        $cashbackTitles = array_map(function ($title) {
            $temp = null;
            $temp = str_replace(' ', '', $title);
            $temp = strtolower($temp);
            return $temp;
        }, $cashbackTitles);
        // $cashbackTitles = array_map('strtolower', $cashbackTitles);
        // $cashbackTitles = json_encode($cashbackTitles);
        $targetSegments = [];
        $currency = Currency::where('account_id', $currentAccountId)->where('isDefault', 1)->first();
        $currency = $currency->currency;
        $segments = Segment::where('account_id', $currentAccountId)->get();

        if ($cashback === null) {
            $cashback = (object)[];
        } else {
            $cashback->for_all == '1'
                ? $targetSegments = ['All']
                : $targetSegments = $cashback->segments;
        }

        return Inertia::render('cashback/pages/CashbackSettingPage', compact('cashback', 'targetSegments', 'currency', 'segments', 'cashbackTitles'));
    }

    public function addCashback(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $ref_key = $this->generateReferenceKey();
        $cashback = Cashback::create([
            'account_id' => $currentAccountId,
            'ref_key' => $ref_key,
            'cashback_title' => $request->input('title'),
            'min_amount' => $request->input('minAmount'),
            'cashback_amount' => $request->input('amount'),
            'capped_amount' => $request->input('cappedAmount'),
            'expire_date' => $request->input('expMonth'),
        ]);

        $saleChannel = [];
        if($request->salesChannel['onlineStore']){
            $saleChannelType = SaleChannel::where('type', 'online-store')->first();
            array_push($saleChannel, $saleChannelType->id);
        }
 
        if($request->salesChannel['miniStore']){
            $saleChannelType = SaleChannel::where('type', 'mini-store')->first();
            array_push($saleChannel, $saleChannelType->id);
        }
        $cashback->saleChannels()->sync($saleChannel);
        
        foreach ($request->input('segments') as $segment) {
            if ($segment === 'All') {
                $cashback->for_all = TRUE;
                $cashback->save();
            } else {
                PeopleCashback::create([
                    'account_id' => $currentAccountId,
                    'segment_id' => $segment['id'],
                    'cashback_id' => $cashback['id'],
                ]);
            }
        }
        
        return response()->json(['status' => 'done']);
    }

    public function updateCashback($id, Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $cashback = Cashback::find($id);
        $cashback->update([
            'cashback_title' => $request->input('title'),
            'min_amount' => $request->input('minAmount'),
            'cashback_amount' => $request->input('amount'),
            'capped_amount' => $request->input('cappedAmount'),
            'expire_date' => $request->input('expMonth')
        ]);

        $saleChannel = [];
        if($request->salesChannel['onlineStore']){
            $saleChannelType = SaleChannel::where('type', 'online-store')->first();
            array_push($saleChannel, $saleChannelType->id);
        }
 
        if($request->salesChannel['miniStore']){
            $saleChannelType = SaleChannel::where('type', 'mini-store')->first();
            array_push($saleChannel, $saleChannelType->id);
        }
        $cashback->saleChannels()->sync($saleChannel);
        
        PeopleCashback::where('account_id', $currentAccountId)->where('cashback_id', $cashback->id)->delete();

        foreach ($request->input('segments') as $segment) {
            if ($segment === 'All') {
                $cashback->for_all = TRUE;
                $cashback->save();
            } else {
                $cashback->for_all = FALSE;
                $cashback->save();
                PeopleCashback::create([
                    'account_id' => $currentAccountId,
                    'segment_id' => $segment['id'],
                    'cashback_id' => $cashback['id'],
                ]);
            }
        }
        return response()->json($cashback);
    }

    public function deleteCashback($id)
    {
        Cashback::find($id)->delete();
        return response()->json(['status' => 'success']);
    }
}
