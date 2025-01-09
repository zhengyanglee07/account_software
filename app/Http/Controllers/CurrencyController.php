<?php

namespace App\Http\Controllers;

use App\Account;
use App\Currency;
use Illuminate\Http\Request;
use App\Traits\AuthAccountTrait;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    use AuthAccountTrait;

    /**
     * get currency setting page
     */
    public function getCurrencySetting()
    {
        $currencySettings = Currency::where('account_id', $this->getCurrentAccountId())
            ->where('exchangeRate', '!=', null)
            ->orderBy('isDefault', 'DESC')
            ->get();
        return Inertia::render('setting/pages/CurrencySettings', compact('currencySettings'));
    }

    /**
     * get currency setting page
     *
     *@param $request request 
     */
    public function getAllCurrrency(request $request)
    {
        $accountId = $request->accountId === null ? $this->getCurrentAccountId() : $request->accountId;
        $currency = Currency::where('account_id', $accountId)
            ->where('exchangeRate', '!=', null)
            ->get();
        return response()->json($currency);
    }

    /**
     * get all currency
     *
     *@param $request request 
     */
    public function getAllCurrrecies()
    {
        $currency = Currency::where('account_id', $this->getCurrentAccountId())->get();
        return response()->json($currency);
    }

    /**
     * get all currency by id 
     *
     *@param $id integer 
     */
    public function getAllCurrrencyById($id)
    {
        $accountId = $id;
        $currency = Currency::where('account_id', $accountId)
            ->where('exchangeRate', '!=', null);
        return response()->json($currency);
    }

    /**
     * save currency setting
     *
     *@param $request request  
     */
    public function saveCurrencySetting(request $request)
    {
        if ($request->isDefault) {
            $account = Account::where('id', $this->getCurrentAccountId())->first();
            $account->currency = $request->selectedCurrency;
            $account->save();
            $resetCurrency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', '1')->first();
            $resetCurrency->isDefault = '0';
            $resetCurrency->save();
        }
        $newCurrency = Currency::firstOrNew(['account_id' => $this->getCurrentAccountId(), 'currency' => $request->selectedCurrency]);
        $newCurrency->currency = $request->selectedCurrency;
        $request->exchangeRate === null ? $request->exchangeRate = '1' : $request->exchangeRate;
        $newCurrency->exchangeRate = !$request->isDefault ? $request->exchangeRate : '1';
        $newCurrency->suggestRate = $request->suggestRate;
        $newCurrency->isDefault = $request->isDefault;
        $newCurrency->decimal_places = $request->decimalPlaces;
        $newCurrency->separator_type = $request->separatorType;
        $newCurrency->prefix = $request->selectedCurrency === 'MYR' ? 'RM' : $request->selectedCurrency;
        $newCurrency->save();
        if ($request->isDefault) {
            $this->resetExchangeRate();
        }
        $notDefaultCurrency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', '0')->first();
        if (isset($notDefaultCurrency)) {
            $lastestCurrencyRounding = $notDefaultCurrency->rounding;
            $this->resetRoundingSettings($lastestCurrencyRounding);
        }
        $allCurrency = Currency::where('account_id', $this->getCurrentAccountId())->whereNotNull('exchangeRate')->orderBy('isDefault', 'DESC')->get();
        return response($allCurrency);
    }


    /**
     * save rounding setting
     *
     *@param $request request  
     */
    public function saveRoundingSettings(request $request)
    {
        $this->resetRoundingSettings($request->rounding);
    }
    public function resetRoundingSettings($isRounding)
    {
        $currencies = Currency::where('account_id', $this->getCurrentAccountId())->get();
        foreach ($currencies as $currency) {
            $currency->rounding = $isRounding;
            if ($currency->isDefault === "1") {
                $currency->rounding = 0;
            }
            $currency->save();
        }
    }

    /**
     * delete currency setting
     *
     *@param $id integer  
     */
    public function deleteCurrencySetting($id)
    {
        $currency = Currency::where('id', $id)->first();
        $currency->exchangeRate = null;
        $currency->save();
        $allCurrency = Currency::where('account_id', $this->getCurrentAccountId())->whereNotNull('exchangeRate')->orderBy('isDefault', 'DESC')->get();
        return response($allCurrency);
    }

    /**
     * get exchange rate throght APi
     *
     *@param $request request  
     */
    public function getExchangeRate(request $request)
    {
        $req_url = 'https://api.exchangerate.host/convert?from=' . $request->defaultCurrency . '&to=' . $request->currentCurrency;
        $response_json = file_get_contents($req_url);
        if (false !== $response_json) {
            try {
                $response = json_decode($response_json);
                if ($response->success === true) {
                    return response()->json($response);
                }
            } catch (Exception $e) {
                // Handle JSON parse error...
            }
        }
    }

    /**
     * get exchange rate throght APi
     */
    public function resetExchangeRate()
    {
        $defaultCurrency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', '1')->first();
        $notDefaultCurrency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', '!=', '1')->get();
        foreach ($notDefaultCurrency as $currency) {
            $req_url = 'https://api.exchangerate.host/convert?from=' . $defaultCurrency->currency . '&to=' . $currency->currency;
            $response_json = file_get_contents($req_url);
            if (false !== $response_json) {
                try {
                    $response = json_decode($response_json);
                    if ($response->success === true) {
                        $currency->suggestRate = $response->result;
                        if ($currency->exchangeRate !== '' && $currency->exchangeRate !== null && !$currency->isDefault) {
                            $currency->exchangeRate = $response->result;
                        }
                        $currency->save();
                    }
                } catch (Exception $e) {
                    // Handle JSON parse error...
                }
            }
        }
    }
    /**
     * get default currency 
     */
    public function getDefaultCurrency()
    {
        $defaultCurrency = Currency::where('account_id', $this->getCurrentAccountId())
            ->where('isDefault', 1)->first()->currency;
        $defaultCurrency = $defaultCurrency == 'MYR' ? 'RM' : $defaultCurrency;
        return response()->json(['currency' => $defaultCurrency]);
    }

    /**
     * get default currency details
     */
    public function getDefaultCurrencyDetails()
    {
        $currencyDetails = Currency::where('account_id', $this->getCurrentAccountId())
            ->where('isDefault', 1)->first();
        return response()->json($currencyDetails);
    }

    public function getCurrency()
    {
        $currencyDetails = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', 1)->first();
        return response()->json($currencyDetails);
    }
}
