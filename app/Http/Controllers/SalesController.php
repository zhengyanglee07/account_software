<?php

namespace App\Http\Controllers;

use App\Account;
use App\ProcessedAddress;
use App\ProcessedContact;
use Inertia\Inertia;
use App\Traits\AuthAccountTrait;
use App\Services\MyTaxService;
use App\Services\MyTaxDocumentService;
use App\Services\RefKeyService;
use Carbon\Carbon;
use App\UsersProduct;
use Illuminate\Http\Request;

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

        $items = UsersProduct::where('account_id', $currentAccountId)->get();

        $contacts = ProcessedContact::with('addresses')->where('account_id', $currentAccountId)->get();


        return Inertia::render('sales/pages/InvoiceDetail', compact(
            'countries',
            'states',
            'numberFormats',
            'classificationCodes',
            'unitTypes',
            'taxTypes',
            'items',
            'contacts'
        ));
    }

    public function submitDocument(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $formData = $request->all();

        $account = Account::with('accountAddress')->find($currentAccountId);
        $accoutBilling = $account->accountAddress->where('is_default_billing', 1)->first();
        $contact = ProcessedContact::with('addresses')->find($formData['contact_id']);
        $contactBilling = $contact->addresses->where('is_default_billing', 1)->first();

        $random = mt_rand(10000, 99999);
        $invoiceNumber = "IV-$random";

        $data = [
            'supplier' => [
                'address' => [
                    'city_name' => $accoutBilling['city'],
                    'postal_zone' => $accoutBilling['zip'],
                    'state_code' => $accoutBilling['state'],
                    'address_line_1' => $accoutBilling['address1'] ?? '',
                    'address_line_2' => $accoutBilling['address2'] ?? '',
                    'address_line_3' => '',
                    'country_code' => $accoutBilling['country_code']
                ],
                'registration_name' => $account->company,
                'contact_no' => $account->contact_no,
                'email' => $account->contact_email,
                'msic_code' => $account->msic_code,
                'msic_code_description' => $account->msic_code_description,
                'tax_id_no' => $account->tax_id_no,
                'reg_no' => $account->reg_no,
                'reg_no_type' => $account->reg_no_type,
                'sst_reg_no' => $account->sst_reg_no,
                'tourism_tax_reg_no' => $account->tourism_tax_reg_no,
            ],
            'buyer' => [
                'address' => [
                    'city_name' => $contactBilling->city,
                    'postal_zone' => $contactBilling->zip,
                    'state_code' => $contactBilling->state_code,
                    'address_line_1' => $contactBilling->address1 ?? '',
                    'address_line_2' => $contactBilling->address2 ?? '',
                    'address_line_3' => '',
                    'country_code' => $contactBilling->country
                ],
                'registration_name' => $contact->name,
                'contact_no' => $contact->phone_number,
                'email' => $contact->email,
                'tax_id_no' => $contact->tax_id_no,
                'reg_no' => $contact->reg_no,
                'reg_no_type' => $contact->reg_no_type,
                'sst_reg_no' => $contact->sst_reg_no,
            ],
            'invoice_number' => $invoiceNumber,
            'typeCode' => '01', //Invoice
            'version' => '1.1',
            'source_currency_code' => 'MYR',
            'currency_code' => 'MYR',
            'tax_exchange_rate' => [
                'calculation_rate' => 0,
            ],
            'invoice_period' => [
                'description' => '',
                'start_date' => '',
                'end_date' => ''
            ],
            'payment_mode' => '01',
            'supplier_bank_account_number' => '',
            'invoice_line' => [
                [
                    'id' => '1',
                    'quantity' => 1,
                    'unit_code' => 'XUN', // https://sdk.myinvois.hasil.gov.my/codes/unit-types/
                    'commodity_classification_code' => '030', //https://sdk.myinvois.hasil.gov.my/codes/classification-codes/
                    'description' => 'Software Development',
                    'origin_country' => '',
                    'currency_code' => 'MYR',
                    'taxes' => [
                        [
                            'tax_type' => '01', //https://sdk.myinvois.hasil.gov.my/codes/tax-types/
                            'per_unit_amount' => 5.00,
                            'number_of_unit' => 1,
                        ],
                        [
                            'tax_type' => '02', //https://sdk.myinvois.hasil.gov.my/codes/tax-types/
                            'tax_rate' => 0.50,
                        ],
                    ],
                    'tax_exemption' => [
                        'reason' => '',
                        'amount' => 0,
                    ],
                    'unit_price' => 100.00,
                    'discount' => [
                        'reason' => '',
                        'amount' => 0,
                        'rate' => 0
                    ],
                    'fee_or_charge' => [
                        'reason' => '',
                        'amount' => 0,
                        'rate' => 0
                    ]
                ]
            ],
            'is_rounding_adjustment' => false,
        ];

        foreach ($formData['form_items'] as $key => $item) {
            $data['invoice_line'][$key] = [
                'id' => (string)($key + 1),
                'quantity' => $item['quantity'] ?? 0,
                'unit_code' => $item['unit_type'] ?? '',
                'commodity_classification_code' => $item['classification_code'] ?? '',
                'description' => $item['description'] ?? 'nothing',
                'origin_country' => $item['origin_country'] ?? 'MYS',
                'currency_code' => $formData['currency_code'],
                'tax_exemption' => [
                    'reason' => $item['tax_exempt_reason'] ?? '',
                    'amount' => $item['tax_exempt_amount'] ?? '',
                ],
                'unit_price' => $item['unit_price'],
                'discount' => [
                    'reason' => '',
                    'rate' => $item['discount']
                ],
                'fee_or_charge' => [
                    'reason' => '',
                    'rate' => 0
                ]
            ];
            foreach ($item['taxes'] as $tax) {
                if (!empty($tax['taxRate'])) {
                    $data['invoice_line'][$key]['taxes'][] = [
                        'tax_type' => $tax['taxType'],
                        'tax_rate' => $tax['taxRate'],
                    ];
                } else {
                    $data['invoice_line'][$key]['taxes'][] = [
                        'tax_type' => $tax['taxType'],
                        'per_unit_amount' => $tax['ratePerUnit'],
                        'number_of_unit' => $tax['numberOfUnits'],
                    ];
                }
            }
        }

        $myTaxDocumentService = new MyTaxDocumentService();
        $myTaxDocumentService->setDocument($data);
        // return response()->json($myTaxDocumentService->getDocument());
        $document = $myTaxDocumentService->getDocument();

        $myTaxService = new MyTaxService();
        return $myTaxService->apiRequest('api/v1.0/documentsubmissions', 'POST', [
            "documents" => [
                [
                    "format" => "JSON",
                    "document" => $document['document_base64'],
                    'documentHash' => $document['document_sha256'],
                    'codeNumber' => $invoiceNumber,
                ]
            ]
        ], true);
    }

    public function saveContact(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $refKeyService = new RefKeyService();


        $formData = $request->all();
        $processedContact =  ProcessedContact::updateOrCreate(
            [
                'account_id' => $currentAccountId,
                'email' => $formData['email'],
            ],
            [
                'contactRandomId' => $refKeyService->getRefKey(new ProcessedContact, 'contactRandomId'),
                'name' => $formData['name'],
                'fname' => $formData['fname'],
                'lname' => $formData['lname'],
                'phone_number' => $formData['phone_number'],
                'entity_type' => $formData['entity_type'],
                'tax_id_no' => $formData['tax_id_no'],
                'reg_no_type' => $formData['reg_no_type'],
                'reg_no' => $formData['reg_no'],
                'old_reg_no' => $formData['old_reg_no'],
                'sst_reg_no' => $formData['sst_reg_no'],
            ]
        );
        foreach ($formData['addresses'] as $address) {
            ProcessedAddress::updateOrCreate([
                'processed_contact_id' => $processedContact->id,
                'name' => $address['name'],
            ], [
                'address1' => $address['address1'],
                'city' => $address['city'],
                'zip' => $address['zip'],
                'country_code' => $address['country_code'],
                'state' => $address['state'],
                'is_default_billing' => $address['is_default_billing'] ? 1 : 0,
                'is_default_shipping' => $address['is_default_shipping'] ? 1 : 0,
            ]);
        }
        $contacts = ProcessedContact::with('addresses')->where('account_id', $currentAccountId)->get();
        return response()->json($contacts);
    }
}
