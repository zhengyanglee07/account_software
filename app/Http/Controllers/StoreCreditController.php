<?php

namespace App\Http\Controllers;

use App\Badges;
use App\Account;
use App\Currency;
use Carbon\Carbon;
use App\StoreCredit;
use App\ProcessedContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class StoreCreditController extends Controller
{
    use PublishedPageTrait;

    /********************************************** for customer account only ***************************************/

    public function getEcommerceUser()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function getCustomerStoreCreditPage()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = "Store Credit Page";
        $user = $this->getEcommerceUser();
        $processedContact = $user->processedContact->setHidden(['created_at', 'updated_at']);
        $storeCredits = $processedContact->storeCredits;
        $totalSpent = (int) $storeCredits->where('source', 'Credit Used')->sum('credit_amount');
        $account = Account::findOrFail($user->account_id);
        $currency = $account->currency;
        $storeCredits->map(function ($storeCredit) {
            // convert value based on currency
            $storeCredit->balance = $this->convertCurrency($storeCredit->balance / 100, $storeCredit->currency, false, true);
            $storeCredit->credit_amount = $this->convertCurrency($storeCredit->credit_amount / 100, $storeCredit->currency, false, true);
            return $storeCredit;
        });

        // $headerFooterMiscSettings = $this->getHeaderFooterSection();
        return Inertia::render(
            'customer-account/pages/StoreCreditPage',
            array_merge($publishPageBaseData, compact('user', 'processedContact', 'storeCredits', 'currency', 'totalSpent', 'pageName'))
        );
    }

    /********************************************** for domain account only ***************************************/

    public function addStoreCredit(Request $request)
    {
        $user = Auth::user();
        $accountCurrency = $user->currentAccount()->currency;
        $currentAccountId = $user->currentAccountId;
        $currencyInfo = Currency::where('account_id', $currentAccountId)->where('currency', $accountCurrency)->first();
        $expireDate = Carbon::now()->addMonth($request->input('expireMonths'))->toDateString();
        $isNotTypeDebit = $request->input('type') === 'Deduct' || $request->input('type') === 'Set';
        $setAmountLeftover = 0;

        foreach ($request->input('contacts') as $id) {
            $selectedRecordsTypes = StoreCredit::where('account_id', $currentAccountId)
                ->where('processed_contact_id', $id)
                ->get()->map(function ($record) {
                    return $record->credit_type;
                });

            $isFirstSetCredit = !($selectedRecordsTypes->contains('Add')) && $request->input('type') === 'Set';
            if ($isNotTypeDebit && !$isFirstSetCredit) {
                $setAmountLeftover = $this->calculateBalance($id, $currentAccountId, $request->input('type'), $request->input('credit'));
            }
            $storeCredit = StoreCredit::create([
                'account_id' => $currentAccountId,
                'processed_contact_id' => $id,
                'credit_amount' => $request->input('credit'),
                'balance' => $isNotTypeDebit && !$isFirstSetCredit ? ($request->input('type') === 'Set' ? $setAmountLeftover : null) : $request->input('credit'),
                'currency' => $currencyInfo->currency,
                'credit_type' => $isFirstSetCredit ? 'Add' : $request->input('type'),
                'source' => $request->input('reason'),
                'reason' => $request->input('reason'),
                'notes' => $request->input('notes'),
                'expire_date' => $request->input('type') === 'Deduct' ? null : $expireDate,
            ]);
        }
        $contacts = ProcessedContact::with('storeCredits')->where('account_id', $currentAccountId)->orderBy('id', 'desc')->get();

        $contacts->map(function ($contact) use ($accountCurrency) {
            // convert value based on currency
            $contact->totalSales = $this->getTotalPrice($contact->orders->toArray(), false);
            $contact->credit_balance = $this->convertCurrency($contact->credit_balance, $accountCurrency);
            return $contact;
        });

        return response()->json($contacts->toArray());
    }

    public function calculateBalance($userId, $accountId, $type, $amount)
    {
        $selectedRecords = StoreCredit::where('account_id', $accountId)
            ->where('processed_contact_id', $userId)
            ->where('credit_type', 'Add')
            ->where('expire_date', '>', Carbon::now())
            ->where(function ($query) use ($type) {
                if ($type !== 'Set') $query->where('balance', '>', 0);
            })
            ->orderBy('expire_date')
            ->get();

        foreach ($selectedRecords as $record) {
            $currencyArray = Currency::where('account_id', $accountId)->pluck('exchangeRate', 'currency')->toArray();
        }
        $totalBalance = 0;
        foreach ($selectedRecords as $record) {
            $totalBalance += ($record->balance / ((float)$currencyArray[$record->currency]));
        }
        // $totalBalance = $selectedRecords->sum('balance');
        $setAmountLeftover = 0;

        if (count($selectedRecords) !== 0) {
            if ($type === 'Deduct') {
                $leftOverCredit = $amount;
                foreach ($selectedRecords as $record) {
                    $balanceCredit = ($record->balance / ((float)$currencyArray[$record->currency])) - $leftOverCredit;
                    if ($balanceCredit >= 0) {
                        $record->balance = $balanceCredit;
                        $record->save();
                        break;
                    }
                    $record->balance = 0;
                    $record->save();
                    $leftOverCredit = abs($balanceCredit);
                }
            } else {
                $setAmountLeftover = $amount - $totalBalance;
                if ($totalBalance > $amount) {
                    $leftOverCredit = $totalBalance - $amount;
                    foreach ($selectedRecords as $record) {
                        $balanceCredit = ($record->balance / ((float)$currencyArray[$record->currency])) - $leftOverCredit;
                        if ($balanceCredit >= 0) {
                            $record->balance = $balanceCredit;
                            $record->save();
                            break;
                        }
                        $record->balance = 0;
                        $record->save();
                        $leftOverCredit = abs($balanceCredit);
                    }
                } else {
                    $leftOverCredit = $amount - $totalBalance;
                    $selectedRecords[count($selectedRecords) - 1]->balance += $leftOverCredit;
                    $selectedRecords[count($selectedRecords) - 1]->save();
                }
            }
        }

        return $setAmountLeftover;
    }

    //
    //*****************************Alternative way to check for expired without scheduler********************************************** */
    //
    // public function checkExpiredCredits(Request $request)
    // {
    //     foreach($request->input('contactIds') as $contact) {
    //         $contact = ProcessedContact::find($contact);
    //         $expiredCredits = $contact->storeCredits()->where('credit_type', 'Add')
    //                             ->where('balance', '>', 0)
    //                             ->where('expire_date', '<=', Carbon::now())
    //                             ->orderBy('expire_date')
    //                             ->get();
    //         if (count($expiredCredits) === 0) {
    //             continue;
    //         }
    //         $totalExpiredCredits = 0;
    //         foreach($expiredCredits as $credit) {
    //             $totalExpiredCredits += $credit->balance;
    //             StoreCredit::create([
    //                 'account_id' => $credit->account_id,
    //                 'processed_contact_id' => $credit->processed_contact_id,
    //                 'credit_amount' => $credit->balance,
    //                 'credit_type' => 'Deduct',
    //                 'source' => 'Credit Expired.',
    //                 'reason' => 'Expired',
    //             ]);
    //             $credit->balance = 0;
    //             $credit->save();
    //         }
    //         $contact->credit_balance -= $totalExpiredCredits;
    //         $contact->save();
    //     }
    //     return response()->json(['status', 'done']);
    // }
}
