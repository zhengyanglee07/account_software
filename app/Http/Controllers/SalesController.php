<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Traits\AuthAccountTrait;
use App\Services\MyTaxService;

class SalesController extends Controller
{
    use AuthAccountTrait;

    public function showAllInvoicePage()
    {
        $currentAccountId = $this->getCurrentAccountId();
        return Inertia::render('sales/pages/AllInvoices');
    }

    public function showInvoiceDetailPage()
    {
        $myTaxService = new MyTaxService();
        // $res = $myTaxService->apiRequest("/api/v1.0/taxpayer/validate/IG29222558100?idType=NRIC&idValue=970412016429");

        $currentAccountId = $this->getCurrentAccountId();

        $countryPath = storage_path() . "/json/countries.json";
        $statePath = storage_path() . "/json/my_states.json";
        $numberFormatsPath = storage_path() . "/json/number_formats.json";

        $countries = json_decode(file_get_contents($countryPath), true);
        $states = json_decode(file_get_contents($statePath), true);
        $numberFormats = json_decode(file_get_contents($numberFormatsPath), true);

        return Inertia::render('sales/pages/InvoiceDetail', compact('countries', 'states', 'numberFormats'));
    }
}
