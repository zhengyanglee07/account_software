<?php

namespace App\Http\Controllers;

use App\Account;
use App\Currency;
use App\ProcessedContact;
use App\Segment;
use App\Services\SegmentService;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\CurrencyConversionTraits;
class SegmentReportController extends Controller
{
    use CurrencyConversionTraits;

    private $segmentService;

    public function __construct(SegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    public function index()
    {
        $currentAccountId = $this->getAccountId();
        $account = Account::findOrFail($currentAccountId);
        $segments = $account->segments;
        $accountCurrencies = Currency::where('account_id', $currentAccountId)->get();
        $defaultCurrency = $accountCurrencies->firstWhere('isDefault', 1)->currency ?? 'MYR'; // currency default to MYR if not specified
        $myrExchangeRate = $accountCurrencies->firstWhere('currency', 'MYR')->exchangeRate ?? 1;

        return view('segmentReport', compact(
            'segments',
            'defaultCurrency',
            'myrExchangeRate',
        ));
    }

    /**
     * Get segment contacts (with orders, important!)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSegmentContacts(Request $request): JsonResponse
    {
        $currentAccountId = $this->getAccountId();
        $segment = Segment::find($request->id); // id = segment id

        $contacts = $segment
            ? $segment->contacts()
            : Account
                ::with('processedContacts.orders')
                ->findOrFail($currentAccountId)
                ->processedContacts;

        foreach($contacts as $contact){
            $contact['orders']->map(function($order){
                $order['total'] = $this->convertCurrency($order['total'],$order['currency'],true, true);
                return $order;
            });
        };

        return response()->json(compact('contacts'));
    }
}
