<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use DOMDocument;
use phpseclib3\File\X509;

class MyTaxDocumentService
{

    protected $document = [
        "AccountingCustomerParty" => [
            // Buyer Element
        ],
        "AccountingSupplierParty" => [
            // Supplier Element
        ],
        "AdditionalDocumentReference" => [
            [
                "ID" => [
                    [
                        "_" => ""
                    ]
                ],
                "ID" => [
                    [
                        "_" => ""
                    ]
                ],
                "ID" => [
                    [
                        "_" => ""
                    ]
                ],
                "ID" => [
                    [
                        "_" => ""
                    ]
                ]
            ]
        ],
        "AllowanceCharge" => [],
        "BillingReference" => [
            [
                "InvoiceDocumentReference" => [
                    [
                        "ID" => [
                            [
                                "_" => ""
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "Delivery" => [
            [
                "DeliveryParty" => [
                    [
                        "PartyLegalEntity" => [
                            [
                                "RegistrationName" => [
                                    [
                                        "_" => ""
                                    ]
                                ]
                            ]
                        ],
                        "PostalAddress" => [
                            [
                                "CityName" => [
                                    [
                                        "_" => ""
                                    ]
                                ],
                                "PostalZone" => [
                                    [
                                        "_" => ""
                                    ]
                                ],
                                "CountrySubentityCode" => [
                                    [
                                        "_" => ""
                                    ]
                                ],
                                "AddressLine" => [
                                    [
                                        "Line" => [
                                            [
                                                "_" => ""
                                            ]
                                        ]
                                    ],
                                    [
                                        "Line" => [
                                            [
                                                "_" => ""
                                            ]
                                        ]
                                    ],
                                    [
                                        "Line" => [
                                            [
                                                "_" => ""
                                            ]
                                        ]
                                    ]
                                ],
                                "Country" => [
                                    [
                                        "IdentificationCode" => [
                                            [
                                                "_" => "",
                                                "listID" => "",
                                                "listAgencyID" => ""
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "PartyIdentification" => [
                            [
                                "ID" => [
                                    [
                                        "_" => "",
                                        "schemeID" => ""
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "Shipment" => [
                    [
                        "ID" => [
                            [
                                "_" => ""
                            ]
                        ],
                        "FreightAllowanceCharge" => [
                            [
                                "ChargeIndicator" => [
                                    [
                                        "_" => true
                                    ]
                                ],
                                "AllowanceChargeReason" => [
                                    [
                                        "_" => ""
                                    ]
                                ],
                                "Amount" => [
                                    [
                                        "_" => 0,
                                        "currencyID" => "MYR"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "DocumentCurrencyCode" => [],
        "ID" => [],
        "InvoiceLine" => [],
        "InvoicePeriod" => [],
        "InvoiceTypeCode" => [
            // E-Invoice Version
            [
                'listVersionID' => '1.1',
            ]
        ],
        "IssueDate" => [],
        "IssueTime" => [],
        "LegalMonetaryTotal" => [],
        "PaymentMeans" => [],
        "PaymentTerms" => [
            [
                'Note' => [
                    [
                        '_' => '',
                    ]
                ]
            ]
        ],
        "PrepaidPayment" => [
            [
                'ID' => [
                    [
                        '_' => ''
                    ]
                ],
                'PaidAmount' => [
                    [
                        '_' => 0,
                        'currencyID' => 'MYR'
                    ]
                ],
                'PaidDate' => [
                    [
                        '_' => ''
                    ]
                ],
                'PaidTime' => [
                    [
                        '_' => ''
                    ]
                ]
            ]
        ],
        "TaxExchangeRate" => [],
        "TaxTotal" => [],
    ];

    /*
     *  Set the supplier Element
     *  @param array $data
     *  $data = [
     *      'address' => [
     *          'city_name' => '',
     *          'postal_zone' => '',
     *          'state_code' => '',
     *          'address_line_1' => '',
     *          'address_line_2' => '',
     *          'address_line_3' => '',
     *          'country_code' => ''
     *          ],
     *      'registration_name' => '',
     *      'contact_no' => '',
     *      'email' => '',
     *      'msic_code' => '',
     *      'msic_code_description' => '',
     *      'tax_id_no' => '',
     *      'reg_no' => '',
     *      'reg_no_type' => '',
     *      'sst_reg_no' => '',
     *      'tourism_tax_reg_no' => ''
     *      ]
     * return void
     * */
    public function setSupplier($data)
    {
        $supplierElement = [
            'AdditionalAccountID' => [
                [
                    'schemeAgencyName' => 'CertEx',
                    '_' => '',
                ]
            ],
            'Party' => [
                [
                    'PartyIdentification' => [],
                    'PostalAddress' => [
                        [
                            'CityName' => [
                                [
                                    '_' => $data['address']['city_name']
                                ]
                            ],
                            'PostalZone' => [
                                [
                                    '_' => $data['address']['postal_zone']
                                ]
                            ],
                            'CountrySubentityCode' => [
                                [
                                    '_' => $data['address']['state_code']
                                ]
                            ],
                            'AddressLine' => [
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_1'],
                                        ]
                                    ]
                                ],
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_2']
                                        ]
                                    ]
                                ],
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_3']
                                        ]
                                    ]
                                ]
                            ],
                            'Country' => [
                                [
                                    'IdentificationCode' => [
                                        [
                                            '_' => $data['address']['country_code'],
                                            "listID" => "ISO3166-1",
                                            "listAgencyID" => "6"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'PartyLegalEntity' => [
                        [
                            'RegistrationName' => [
                                [
                                    '_' => $data['registration_name']
                                ]
                            ]
                        ]
                    ],
                    'Contact' => [
                        [
                            'Telephone' => [
                                [
                                    '_' => $data['contact_no']
                                ]
                            ],
                            'ElectronicMail' => [
                                [
                                    '_' => $data['email']
                                ]
                            ]
                        ]
                    ],
                    "IndustryClassificationCode" => [
                        [
                            "_" => $data['msic_code'],
                            "name" => $data['msic_code_description']
                        ]
                    ]
                ]
            ]
        ];

        if (!empty($data['tax_id_no'])) {
            $supplierElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['tax_id_no'],
                        'schemeID' => 'TIN'
                    ]
                ]
            ];
        }

        if (!empty($data['reg_no'])) {
            $supplierElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['reg_no'],
                        'schemeID' => $data['reg_no_type']
                    ]
                ]
            ];
        }

        if (!empty($data['sst_reg_no'])) {
            $supplierElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['sst_reg_no'],
                        'schemeID' => 'SST'
                    ]
                ]
            ];
        }

        if (!empty($data['tourism_tax_reg_no'])) {
            $supplierElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['tourism_tax_reg_no'],
                        'schemeID' => 'TTX'
                    ]
                ]
            ];
        }

        $this->document['AccountingSupplierParty'][0] = $supplierElement;
    }

    /*
     * Set the buyer buyer Element
     * @param array $data
     * $data = [
     *      'address' => [
     *          'city_name' => '',
     *          'postal_zone' => '',
     *          'state_code' => '',
     *          'address_line_1' => '',
     *          'address_line_2' => '',
     *          'address_line_3' => '',
     *          'country_code' => ''
     *      ],
     *     'registration_name' => '',
     *     'contact_no' => '',
     *     'email' => '',
     *     'tax_id_no' => '',
     *     'reg_no' => '',
     *     'reg_no_type' => '',
     *     'sst_reg_no' => ''
     *  ]
     *
    */
    public function setBuyerElement($data)
    {
        $buyerElement = [
            'Party' => [
                [
                    'PartyIdentification' => [],
                    'PostalAddress' => [
                        [
                            'CityName' => [
                                [
                                    '_' => $data['address']['city_name']
                                ]
                            ],
                            'PostalZone' => [
                                [
                                    '_' => $data['address']['postal_zone']
                                ]
                            ],
                            'CountrySubentityCode' => [
                                [
                                    '_' => $data['address']['state_code']
                                ]
                            ],
                            'AddressLine' => [
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_1'],
                                        ]
                                    ]
                                ],
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_2']
                                        ]
                                    ]
                                ],
                                [
                                    'Line' => [
                                        [
                                            '_' => $data['address']['address_line_3']
                                        ]
                                    ]
                                ]
                            ],
                            'Country' => [
                                [
                                    'IdentificationCode' => [
                                        [
                                            '_' => $data['address']['country_code'],
                                            "listID" => "ISO3166-1",
                                            "listAgencyID" => "6"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'PartyLegalEntity' => [
                        [
                            'RegistrationName' => [
                                [
                                    '_' => $data['registration_name']
                                ]
                            ]
                        ]
                    ],
                    'Contact' => [
                        [
                            'Telephone' => [
                                [
                                    '_' => $data['contact_no']
                                ]
                            ],
                            'ElectronicMail' => [
                                [
                                    '_' => $data['email']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if (!empty($data['tax_id_no'])) {
            $buyerElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['tax_id_no'],
                        'schemeID' => 'TIN'
                    ]
                ]
            ];
        }

        if (!empty($data['reg_no'])) {
            $buyerElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['reg_no'],
                        'schemeID' => $data['reg_no_type']
                    ]
                ]
            ];
        }

        if (!empty($data['sst_reg_no'])) {
            $buyerElement['Party'][0]['PartyIdentification'][] = [
                'ID' => [
                    [
                        '_' => $data['sst_reg_no'],
                        'schemeID' => 'SST'
                    ]
                ]
            ];
        }

        $this->document['AccountingCustomerParty'][0] = $buyerElement;
    }

    public function setInvoiceLineElement($datas)
    {
        $this->priceCalculator();
        foreach ($datas as $data) {
            $subTotal = $this->priceCalculator([$data['unit_price'], '*', $data['quantity']]);
            $discount = $this->priceCalculator([$data['discount']['rate'], '*', $data['discount']['rate']]);
            $feeOrCharge = $this->priceCalculator([$subTotal, '*', $data['fee_or_charge']['rate']]);
            $totalExclusiveTax = $this->priceCalculator([$subTotal, '+', $feeOrCharge, '-', $discount]);
            $invoiceLineElement = [
                'ID' => [
                    [
                        '_' => $data['id']
                    ]
                ],
                'InvoicedQuantity' => [
                    [
                        '_' => $this->toDecimal($data['quantity']),
                        'unitCode' => $data['unit_code']
                    ]
                ],
                'Item' => [
                    [
                        'CommodityClassification' => [
                            [
                                'ItemClassificationCode' => [
                                    [
                                        '_' => $data['commodity_classification_code'],
                                        'listID' => 'CLASS'
                                    ]
                                ]
                            ]
                        ],
                        'Description' => [
                            [
                                '_' => $data['description']
                            ]
                        ],
                        'OriginCountry' => [
                            [
                                'IdentificationCode' => [
                                    [
                                        '_' => $data['origin_country']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'ItemPriceExtension' => [
                    [
                        'Amount' => [
                            [
                                '_' => $subTotal,
                                'currencyID' => $data['currency_code']
                            ]
                        ]
                    ]
                ],
                'LineExtensionAmount' => [
                    [
                        '_' => $totalExclusiveTax,
                        'currencyID' => $data['currency_code']
                    ]
                ],
                'Price' => [
                    [
                        'PriceAmount' => [
                            [
                                '_' => $this->toDecimal($data['unit_price']),
                                'currencyID' => $data['currency_code']
                            ]
                        ]
                    ]
                ],
                'TaxTotal' => [
                    [
                        'TaxAmount' => [
                            [
                                '_' => 0,
                                'currencyID' => $data['currency_code']
                            ]
                        ],
                        'TaxSubtotal' => []
                    ]
                ],
                'AllowanceCharge' => [
                    [
                        'ChargeIndicator' => [
                            [
                                '_' => false
                            ]
                        ],
                        'AllowanceChargeReason' => [
                            [
                                '_' => $data['discount']['reason']
                            ]
                        ],
                        'Amount' => [
                            [
                                '_' => $discount,
                                'currencyID' => $data['currency_code']
                            ]
                        ],
                        'MultiplierFactorNumeric' => [
                            [
                                '_' => $this->toDecimal($data['discount']['rate'])
                            ]
                        ]
                    ],
                    [
                        'ChargeIndicator' => [
                            [
                                '_' => true
                            ]
                        ],
                        'AllowanceChargeReason' => [
                            [
                                '_' => $data['fee_or_charge']['reason']
                            ]
                        ],
                        'Amount' => [
                            [
                                '_' => $feeOrCharge,
                                'currencyID' => $data['currency_code']
                            ]
                        ],
                        'MultiplierFactorNumeric' => [
                            [
                                '_' => $this->toDecimal($data['fee_or_charge']['rate'])
                            ]
                        ]
                    ]
                ]
            ];

            $taxableAmount = $totalExclusiveTax;

            if (!empty($data['tax_exemption']['amount']) && $data['tax_exemption']['amount'] > 0) {
                $taxableAmount = $this->priceCalculator([$taxableAmount, '-', $data['tax_exemption']['amount']]);
            }

            $totalTaxAmount = 0;
            $totalTaxAmountWithoutExemption = 0;
            if (!empty($data['taxes'])) {
                foreach ($data['taxes'] as $tax) {
                    $taxAmount = 0;
                    $taxAmountWithoutExemption = 0;
                    if (!empty($tax['tax_rate'])) {
                        $taxAmountWithoutExemption = $this->priceCalculator([$totalExclusiveTax, '*', $tax['tax_rate'], '/', 100]);
                        $taxAmount = $this->priceCalculator([$taxableAmount, '*', $tax['tax_rate'], '/', 100]);
                        $invoiceLineElement['TaxTotal'][0]['TaxSubtotal'][] = [
                            'Percent' => [
                                [
                                    '_' => $this->toDecimal($tax['tax_rate'])
                                ]
                            ],
                            'TaxAmount' => [
                                [
                                    '_' => $taxAmount,
                                    'currencyID' => $data['currency_code']
                                ]
                            ],
                            'TaxableAmount' => [
                                [
                                    '_' => $taxAmount,
                                    'currencyID' => $data['currency_code']
                                ]
                            ],
                            'TaxCategory' => [
                                [
                                    'ID' => [
                                        [
                                            '_' => $tax['tax_type'],
                                        ]
                                    ],
                                    'TaxScheme' => [
                                        [
                                            'ID' => [
                                                [
                                                    'schemeAgencyID' => '6',
                                                    'schemeID' => "UN/ECE 5153",
                                                    '_' => 'OTH'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        $taxAmountWithoutExemption = $this->priceCalculator([$tax['per_unit_amount'], '*', $tax['number_of_unit']]);
                        $taxAmount = $this->priceCalculator([$taxableAmount, '/', $totalExclusiveTax, '*',  $tax['per_unit_amount'], '*', $tax['number_of_unit']]);

                        $invoiceLineElement['TaxTotal'][0]['TaxSubtotal'][] = [
                            'BaseUnitMeasure' => [
                                [
                                    '_' => $this->toDecimal($tax['number_of_unit']),
                                    'unitCode' => $data['unit_code']
                                ]
                            ],
                            'PerUnitAmount' => [
                                [
                                    '_' => $this->toDecimal($tax['per_unit_amount']),
                                    'currencyID' => $data['currency_code']
                                ]
                            ],
                            'TaxAmount' => [
                                [
                                    '_' => $taxAmount,
                                    'currencyID' => $data['currency_code']
                                ]
                            ],
                            'TaxableAmount' => [
                                [

                                    '_' => $taxableAmount,
                                    'currencyID' => $data['currency_code']
                                ]
                            ],
                            'TaxCategory' => [
                                [
                                    'ID' => [
                                        [
                                            '_' => $tax['tax_type'],
                                        ]
                                    ],
                                    'TaxScheme' => [
                                        [
                                            'ID' => [
                                                [
                                                    'schemeAgencyID' => '6',
                                                    'schemeID' => "UN/ECE 5153",
                                                    '_' => 'OTH'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                    $totalTaxAmount += $taxAmount;
                    $totalTaxAmountWithoutExemption += $taxAmountWithoutExemption;
                }
            }

            $invoiceLineElement['TaxTotal'][0]['TaxSubtotal'][] = [
                'TaxAmount' => [
                    [
                        '_' => $this->priceCalculator([$totalTaxAmountWithoutExemption, '-', $totalTaxAmount]),
                        'currencyID' => $data['currency_code']
                    ]
                ],
                'TaxCategory' => [
                    [
                        'ID' => [
                            [
                                '_' => 'E',
                            ]
                        ],
                        'Percent' => [
                            [
                                '_' => 0
                            ]
                        ],
                        'TaxExemptionReason' => [
                            [
                                '_' => $data['tax_exemption']['reason']
                            ]
                        ],
                        'TaxScheme' => [
                            [
                                'ID' => [
                                    [
                                        'schemeAgencyID' => '6',
                                        'schemeID' => "UN/ECE 5153",
                                        '_' => 'OTH'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'TaxableAmount' => [
                    [
                        '_' => $data['tax_exemption']['amount'],
                        'currencyID' => $data['currency_code']
                    ]
                ]
            ];
            $invoiceLineElement['TaxTotal'][0]['TaxAmount'][0]['_'] = $totalTaxAmount;
            $this->document['AllowanceCharge'][] = $invoiceLineElement['AllowanceCharge'][0];
            $this->document['InvoiceLine'][] = $invoiceLineElement;
        }
    }

    public function setSignatureElement($data)
    {
        $privateKeyContent = file_get_contents(storage_path('keys/private_key.pem'));
        $certificateContent = file_get_contents(storage_path('keys/certificate.pem'));

        if (!$privateKeyContent || !$certificateContent) {
            throw new \Exception('Private key or certificate missing.');
        }

        $privateKey = openssl_pkey_get_private($privateKeyContent);
        if (!$privateKey) {
            throw new \Exception('Unable to load private key.');
        }

        $minifiedJSON = json_encode($data, JSON_UNESCAPED_SLASHES);
        $hash = hash('sha256', $minifiedJSON, true);

        $docDigest = base64_encode($hash);

        $sign = openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$sign) {
            throw new \Exception('Unable to sign the document.');
        }
        $signatureBase64 = base64_encode($signature);

        $certInfo = openssl_x509_parse($certificateContent);

        $certRawData = trim(preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----/', '', $certificateContent));
        $certRawData =  preg_replace('/\s+/', '', $certRawData);

        $certHash = hash('sha256', base64_decode($certRawData), true);
        $certDigest = base64_encode($certHash);

        $utcTimestamp = Carbon::now()->toDateTimeString() . 'Z';
        $utcTimestamp = str_replace(' ', 'T', $utcTimestamp);

        $signedProperties =   [
            "Target" => "signature",
            "SignedProperties" => [
                [
                    "Id" => "id-xades-signed-props",
                    "SignedSignatureProperties" => [
                        [
                            "SigningTime" => [
                                [
                                    "_" => $utcTimestamp
                                ]
                            ],
                            "SigningCertificate" => [
                                [
                                    "Cert" => [
                                        [
                                            "CertDigest" => [
                                                [
                                                    "DigestMethod" => [
                                                        [
                                                            "_" => "",
                                                            "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"
                                                        ]
                                                    ],
                                                    "DigestValue" => [
                                                        [
                                                            "_" => $certDigest
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            "IssuerSerial" => [
                                                [
                                                    "X509IssuerName" => [
                                                        [
                                                            "_" => $certInfo['name']
                                                        ]
                                                    ],
                                                    "X509SerialNumber" => [
                                                        [
                                                            "_" => $certInfo['serialNumberHex']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $signedProps = json_encode($signedProperties, JSON_UNESCAPED_SLASHES);
        $signedPropsHash = hash('sha256', $signedProps, true);
        $propsDigest = base64_encode($signedPropsHash);

        $signatureElement = [
            'Id' => 'signature',
            "KeyInfo" => [
                [
                    "X509Data" => [
                        [
                            "X509Certificate" => [
                                [
                                    "_" => $certRawData,
                                ]
                            ],
                            "X509IssuerSerial" => [
                                [
                                    "X509IssuerName" => [
                                        [
                                            '_' => $certInfo['name']
                                        ]
                                    ],
                                    "X509SerialNumber" => [
                                        [
                                            "_" => $certInfo['serialNumberHex']
                                        ]
                                    ]
                                ]
                            ],
                            "X509SubjectName" => [
                                [
                                    "_" => $certInfo['name']
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            "Object" => [
                [
                    "QualifyingProperties" => [
                        $signedProperties
                    ]
                ]
            ],
            "SignatureValue" => [
                [
                    "_" => $signatureBase64
                ]
            ],
            "SignedInfo" => [
                [
                    "Reference" => [
                        [
                            "DigestMethod" => [
                                [
                                    "_" => "",
                                    "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"
                                ]
                            ],
                            "DigestValue" => [
                                [
                                    "_" => $propsDigest
                                ]
                            ],
                            "Type" => "http://uri.etsi.org/01903/v1.3.2#SignedProperties",
                            "URI" => "#id-xades-signed-props"
                        ],
                        [
                            "DigestMethod" => [
                                [
                                    "_" => "",
                                    "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"
                                ]
                            ],
                            /*This is the document digest value that would be created using HEX-to Base64 Encoder applied to the entire transformed document content. This is the value that would be signed using the private key. [Reference field: DocDigest]*/
                            "DigestValue" => [
                                [
                                    "_" => $docDigest
                                ]
                            ],
                            "Type" => "",
                            "URI" => ""
                        ]
                    ],
                    "SignatureMethod" => [
                        [
                            "_" => "",
                            "Algorithm" => "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"
                        ]
                    ]
                ]
            ],
        ];
        $this->document['UBLExtensions'] = [
            [
                'UBLExtension' => [
                    [
                        'ExtensionURI' => [
                            [
                                '_' => "urn:oasis:names:specification:ubl:dsig:enveloped:xades"
                            ]
                        ],
                        'ExtensionContent' => [
                            [
                                'UBLDocumentSignatures' => [
                                    [
                                        'SignatureInformation' => [
                                            [
                                                'ID' => [
                                                    [
                                                        "_" => "urn:oasis:names:specification:ubl:signature:1"
                                                    ]
                                                ],
                                                'ReferencedSignatureID' => [
                                                    [
                                                        "_" => 'urn:oasis:names:specification:ubl:signature:Invoice'
                                                    ]
                                                ],
                                                'Signature' => [
                                                    $signatureElement
                                                ]
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->document['Signature'] = [
            [
                'ID' => [
                    [
                        '_' => 'urn:oasis:names:specification:ubl:signature:Invoice'
                    ]
                ],
                'SignatureMethod' => [
                    [
                        '_' => 'urn:oasis:names:specification:ubl:dsig:enveloped:xades'
                    ]
                ]
            ]
        ];
    }


    public function setDocument($data)
    {
        $data = [
            'supplier' => [
                'address' => [
                    'city_name' => 'RAWANG',
                    'postal_zone' => '48000',
                    'state_code' => '10',
                    'address_line_1' => '5570 JALAN NGP 1/1',
                    'address_line_2' => 'TAMAN JIJAU',
                    'address_line_3' => '',
                    'country_code' => 'MYS'
                ],
                'registration_name' => 'LEE ZHENG YANG',
                'contact_no' => '601123861608',
                'email' => 'zhengyangz1007@gmail.com',
                'msic_code' => '95122',
                'msic_code_description' => 'Repair and maintenance of cellular phones',
                'tax_id_no' => 'IG50668341090',
                'reg_no' => '011007101175',
                'reg_no_type' => 'NRIC',
                'sst_reg_no' => '',
                'tourism_tax_reg_no' => ''
            ],
            'buyer' => [
                'address' => [
                    'city_name' => 'KLUANG',
                    'postal_zone' => '86000',
                    'state_code' => '01',
                    'address_line_1' => '19,JALAN SURIA 4/1,TAMAN PURNAMA',
                    'address_line_2' => '',
                    'address_line_3' => '',
                    'country_code' => 'MYS'
                ],
                'registration_name' => 'CHONG JING ZE',
                'contact_no' => '0137679611',
                'email' => 'cjzchong1@gmail.com',
                'tax_id_no' => 'IG29222558100',
                'reg_no' => '970412016429',
                'reg_no_type' => 'NRIC',
                'sst_reg_no' => 'N/A'
            ],
            'invoice_number' => '2452104785881',
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
            'line_extension_amount' => 100.00,
            'tax_exclusive_amount' => 100.00,
            'tax_inclusive_amount' => 106.00,
            'allowance_total_amount' => 0.00,
            'charge_total_amount' => 0.00,
            'payable_amount' => 106.00,
            'payable_rounding_amount' => 0.00,
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
                    'subtotal' => 100.00,
                    'currency_code' => 'MYR',
                    'total_excluding_tax' => 100.00,
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

        $this->setSupplier($data['supplier']);
        $this->setBuyerElement($data['buyer']);
        $this->setInvoiceLineElement($data['invoice_line']);
        if (!empty($data['version'])) {
            $this->document['InvoiceTypeCode'][0]['listVersionID'] = $data['version'];
        }
        $this->document['InvoiceTypeCode'][0]['_'] = $data['typeCode'];
        $this->document['ID'][0]['_'] = $data['invoice_number'];
        $now = Carbon::now('UTC');
        $this->document['IssueDate'][0]['_'] = $now->toDateString();
        $this->document['IssueTime'][0]['_'] = $now->toTimeString() . 'Z';
        $this->document['DocumentCurrencyCode'][0]['_'] = $data['currency_code'];
        $this->document['TaxExchangeRate'][0] = [
            'CalculationRate' => [
                [
                    '_' => $data['tax_exchange_rate']['calculation_rate']
                ]
            ],
            'SourceCurrencyCode' => [
                [
                    '_' => $data['source_currency_code']
                ]
            ],
            'TargetCurrencyCode' => [
                [
                    '_' => $data['currency_code']
                ]
            ]
        ];
        $this->document['InvoicePeriod'][0] = [
            'Description' => [
                [
                    '_' => $data['invoice_period']['description']
                ]
            ],
            'StartDate' => [
                [
                    '_' => $data['invoice_period']['start_date']
                ]
            ],
            'EndDate' => [
                [
                    '_' => $data['invoice_period']['end_date']
                ]
            ]
        ];

        $this->document['PaymentMeans'][0] = [
            'PaymentMeansCode' => [
                [
                    '_' => $data['payment_mode']
                ]
            ],
            'PayeeFinancialAccount' => [
                [
                    'ID' => [
                        [
                            '_' => $data['supplier_bank_account_number']
                        ]
                    ]
                ]
            ]

        ];

        $invoiceLine = $this->document['InvoiceLine'];
        $totalLineExtensionAmount = 0;
        $totalAllowanceAmount = 0;
        $totalChargeAmount = 0;

        $totalTaxAmount = 0;
        $taxSubTotal = [];
        foreach ($invoiceLine as $line) {
            $totalTaxAmount = $this->priceCalculator([$totalTaxAmount, '+', $line['TaxTotal'][0]['TaxAmount'][0]['_']]);
            $totalLineExtensinAmount = $this->priceCalculator([$totalLineExtensionAmount, '+', $line['LineExtensionAmount'][0]['_']]);
            foreach ($line['AllowanceCharge'] as $allowance) {
                if ($allowance['ChargeIndicator'][0]['_'] === true)
                    $totalChargeAmount = $this->priceCalculator([$totalChargeAmount, '+', $allowance['Amount'][0]['_']]);
                else
                    $totalAllowanceAmount = $this->priceCalculator([$totalAllowanceAmount, '+', $allowance['Amount'][0]['_']]);
            }
            foreach ($line['TaxTotal'][0]['TaxSubtotal'] as $subTax) {
                if ($subTax['TaxAmount'][0]['_'] > 0) {
                    $taxSubTotal[] = [
                        'TaxAmount' => $subTax['TaxAmount'],
                        'TaxCategory' => $subTax['TaxCategory'],
                        'TaxableAmount' => $subTax['TaxableAmount'],
                    ];
                }
            }
        }

        $taxInclusiveAmount = $this->priceCalculator([$totalLineExtensionAmount, '+', $totalTaxAmount]);
        $payableAmountWithoutRounding = $taxInclusiveAmount;

        $payableAmount = $payableAmountWithoutRounding;
        $payableRoundingAmount = 0;

        if ($data['is_rounding_adjustment']) {
            $payableAmountWithRounding = $this->getRoundingAmount($payableAmountWithoutRounding);
            $payableRoundingAmount = $this->priceCalculator([$payableAmountWithRounding, '-', $payableAmountWithoutRounding]);
            $payableAmount = $payableAmountWithRounding;
        }


        $this->document['TaxTotal'][0] = [
            'TaxAmount' => [
                [
                    '_' => $totalTaxAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'TaxSubtotal' => $taxSubTotal
        ];


        $this->document['LegalMonetaryTotal'][0] = [
            'LineExtensionAmount' => [
                [
                    '_' => $totalLineExtensionAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'TaxExclusiveAmount' => [
                [
                    '_' => $totalLineExtensionAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'TaxInclusiveAmount' => [
                [
                    '_' => $taxInclusiveAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'AllowanceTotalAmount' => [
                [
                    '_' => $totalAllowanceAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'ChargeTotalAmount' => [
                [
                    '_' => $totalChargeAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'PayableAmount' => [
                [
                    '_' => $payableAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
            'PayableRoundingAmount' => [
                [
                    '_' => $payableRoundingAmount,
                    'currencyID' => $data['currency_code']
                ]
            ],
        ];


        $this->setSignatureElement($this->getDocument()['document']);
    }


    public function getDocument()
    {
        $document = [
            'Invoice' => [
                $this->document
            ],
            '_A' => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
            '_B' => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
            '_D' => "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
        ];

        $documentEncoded = json_encode($document, true);

        return [
            'document' => $document,
            'document_base64' => base64_encode($documentEncoded),
            'document_sha256' => hash('sha256', $documentEncoded),
        ];
    }

    private function getRoundingAmount($amount)
    {
        $cents = $amount * 100;
        $remainder = $cents % 5;

        if ($remainder < 2.5) {
            $roundedCents = $cents - $remainder;
        } else {
            $roundedCents = $cents + (5 - $remainder);
        }
        return $roundedCents / 100;
    }

    private function toDecimal($value)
    {
        return $value;
        // return (float)number_format($value, 2);
    }
    private function priceCalculator($formulars = [], $isDecimal = true)
    {
        $formularStr = implode(' ', $formulars);
        $result = eval("return $formularStr;");
        // return (float)($isDecimal ? $this->toDecimal($result) : $result);
        return (float)$result;
    }
}
