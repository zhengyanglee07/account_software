<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Traits\AuthAccountTrait;
use App\Services\MyTaxService;
use App\Services\MyTaxDocumentService;
use Carbon\Carbon;

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
        // $dn = [
        //     "countryName"            => "MY",  // Country (C)
        //     "stateOrProvinceName"    => "Rawang",  // State or Province
        //     "localityName"           => "Selangor",  // City
        //     "organizationName"       => "Lee Zheng Yang",  // Organization (O)
        //     /*"organizationalUnitName" => "IT Dep",  // Organization Unit (OU)*/
        //     "commonName"             => "Lee Zheng Yang",  // Common Name (CN)
        //     "emailAddress"           => "zhengyangz1007@gmail.com",  // Email (E)
        //     "organizationIdentifier" => "IG50668341090",  // Organization Identifier
        //     "serialNumber"           => "011007101175",  // Serial Number
        // ];
        // $privateKey = openssl_pkey_new([
        //     "private_key_bits" => 2048,
        //     "private_key_type" => OPENSSL_KEYTYPE_RSA,
        // ]);
        // $configPath = storage_path('keys/openssl.cnf');
        // // Generate a certificate signing request
        // $csr = openssl_csr_new($dn, $privateKey, [
        //     "config" => $configPath,
        // ]);
        //
        // // Self-sign the certificate
        // $certificate = openssl_csr_sign($csr, null, $privateKey, 365, ['config' => $configPath]); // Valid for 365 days
        /*/// sve to storage/key/certificate.pem*/
        /*openssl_x509_export_to_file($certificate, storage_path('keys/certificate.pem'));*/
        /*openssl_pkey_export_to_file($privateKey, storage_path('keys/private_key.pem'));*/


        $myTaxService = new MyTaxService();
        $myTaxDocumentService = new MyTaxDocumentService();
        $myTaxDocumentService->setDocument([]);


        // return response()->json($myTaxDocumentService->getDocument());

        /*dd($myTaxDocumentService->getDocument());*/


        $currentAccountId = $this->getCurrentAccountId();

        $countryPath = storage_path() . "/json/countries.json";
        $statePath = storage_path() . "/json/my_states.json";
        $numberFormatsPath = storage_path() . "/json/number_formats.json";
        $classificationCodePath = storage_path() . "/json/item_classification_codes.json";
        $unitTypePath = storage_path() . "/json/item_unit_types.json";
        $taxTypePath = storage_path() . "/json/tax_types.json";

        $countries = json_decode(file_get_contents($countryPath), true);
        $states = json_decode(file_get_contents($statePath), true);
        $numberFormats = json_decode(file_get_contents($numberFormatsPath), true);
        $classificationCodes = json_decode(file_get_contents($classificationCodePath), true);
        $unitTypes = json_decode(file_get_contents($unitTypePath), true);
        $taxTypes = json_decode(file_get_contents($taxTypePath), true);

        return Inertia::render('sales/pages/InvoiceDetail', compact(
            'countries',
            'states',
            'numberFormats',
            'classificationCodes',
            'unitTypes',
            'taxTypes'
        ));
    }
}
