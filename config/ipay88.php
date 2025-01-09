<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Demo account
    |--------------------------------------------------------------------------
    |
    | List merchant code of demo account.
	| For demo acount will use transaction amount specified by ipay88 for payment test
	| For more detail for transaction limit refer 'transaction_amount' below
	| 
    */
	'demo_accounts' => [
		'M03358',
	],

	/*
    |--------------------------------------------------------------------------
    | Gateway
    |--------------------------------------------------------------------------
    |
    | Configure gateway used for ipay88 OPSG
	| Available gateway: myr_gateway, multi_currency_gateway
    |
    */
	'gateway' => env('IPAY88_PAYMENT_GATEWAY', 'myr_gateway'),

	/*
    |--------------------------------------------------------------------------
    | Hash Signature
    |--------------------------------------------------------------------------
    |
    | Signature type used for generate hash that will be included in request
    | of every transcation
    |
    */
	'signature_type' => 'SHA256',

	/*
    |--------------------------------------------------------------------------
    | Response url
    |--------------------------------------------------------------------------
    |
    | Configure for response url after payment from ipay88 OPSG
    |
    */
	/**
	 * Path to get payment response from ipay88 opsg after performing payment
	 * Url = https:// . {os/ms domain} . {response_path}
	 */
	'response_path' => '/ipay88/response',
	/**
	 * Urt to get response asynchronously even if normal response page failed to get status
	 * due to a closed web browser, internet connection timeout and etc.
	 * 
	 */
	'backend_url' => env('APP_URL') . '/ipay88/callback',

	/*
    |--------------------------------------------------------------------------
    | OPSG URL
    |--------------------------------------------------------------------------
    |
    | Request and requery url to ipay88 OPSG.
	| Refer at: https://hypershapes.slack.com/files/U02AK5S2N9H/F04A1EX5MBP/ipay88_technical_spec_v1.6.4.pdf
    |
    */
	'opsg_request_url' => 'https://payment.ipay88.com.my/epayment/entry.asp',
	'opsg_requery_url' => 'https://payment.ipay88.com.my/epayment/enquiry.asp',

	/*
    |--------------------------------------------------------------------------
    | Payment ID
    |--------------------------------------------------------------------------
    |
    | List of payment id defined by ipay88
	|
    | For MYR gateway refer at: https://hypershapes.slack.com/files/U02AK5S2N9H/F04AH2UAPR7/appendix_i.pdf
	| For Multi-currency gateway refer at: https://hypershapes.slack.com/files/U02AK5S2N9H/F049PSWJ03Z/appendix_ii.pdf
    |
    */
	'payment_id' => [
		'myr_gateway' => [
			2 => ['name' => 'Credit Card (MYR)', 'logo' => ''],
			6 => ['name' => 'Maybank2U', 'logo' => ''],
			8 => ['name' => 'Alliance Online', 'logo' => ''],
			10 => ['name' => 'AmOnline', 'logo' => ''],
			14 => ['name' => 'RHB Online', 'logo' => ''],
			15 => ['name' => 'Hong Leong Online', 'logo' => ''],
			20 => ['name' => 'CIMB Click', 'logo' => ''],
			22 => ['name' => 'Web Cash', 'logo' => ''],
			31 => ['name' => 'Public Bank Online', 'logo' => ''],
			48 => ['name' => 'PayPal (MYR)', 'logo' => ''],
			55 => ['name' => 'Credit Card (MYR) Pre-Auth', 'logo' => ''],
			102 => ['name' => 'Bank Rakyat Internet Banking', 'logo' => ''],
			103 => ['name' => 'Affin Online', 'logo' => ''],
			122 => ['name' => 'Pay4Me (Delay payment)', 'logo' => ''],
			124 => ['name' => 'BSN Online', 'logo' => ''],
			134 => ['name' => 'Bank Islam', 'logo' => ''],
			152 => ['name' => 'UOB', 'logo' => ''],
			163 => ['name' => 'Hong Leong PEx+ (QR Payment)', 'logo' => ''],
			166 => ['name' => 'Bank Muamalat', 'logo' => ''],
			167 => ['name' => 'OCBC', 'logo' => ''],
			168 => ['name' => 'Standard Chartered Bank', 'logo' => ''],
			173 => ['name' => 'CIMB Virtual Account (Delay payment)', 'logo' => ''],
			198 => ['name' => 'HSBC Online Banking', 'logo' => ''],
			199 => ['name' => 'Kuwait Finance House', 'logo' => ''],
			210 => ['name' => 'Boost Wallet', 'logo' => ''],
			243 => ['name' => 'VCash', 'logo' => ''],
		],
		'multi_currency_gateway' => [
			25 => ['name' => 'Credit Card (USD)', 'logo' => ''],
			35 => ['name' => 'Credit Card (GBP)', 'logo' => ''],
			36 => ['name' => 'Credit Card (THB)', 'logo' => ''],
			37 => ['name' => 'Credit Card (CAD)', 'logo' => ''],
			38 => ['name' => 'Credit Card (SGD)', 'logo' => ''],
			39 => ['name' => 'Credit Card (AUD)', 'logo' => ''],
			40 => ['name' => 'Credit Card (MYR)', 'logo' => ''],
			41 => ['name' => 'Credit Card (EUR)', 'logo' => ''],
			42 => ['name' => 'Credit Card (HKD)', 'logo' => ''],
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Supported Currency
    |--------------------------------------------------------------------------
    |
    | List of supported currency for all gateway. 
	| Structure of currency lists is 'currency code' as key associate with its 'currency description'
	|
    | For MYR gateway refer at: https://hypershapes.slack.com/files/U02AK5S2N9H/F04AH2UAPR7/appendix_i.pdf
	| For Multi-currency gateway refer at: https://hypershapes.slack.com/files/U02AK5S2N9H/F049PSWJ03Z/appendix_ii.pdf
    |
    */
	'currency' => [
		'myr_gateway' => [
			'MYR' => 'Malaysian Ringgit',
		],
		'multi_currency_gateway' => [
			'AUD' => 'Australian Dollar',
			'CAD' => 'Canadian Dollar',
			'EUR' => 'Euro',
			'GBP' => 'Pound Sterling',
			'HKD' => 'Hong Kong Dollar',
			'MYR' => 'Malaysian Ringgit',
			'SGD' => 'Singapore Dollar ',
			'THB' => 'Thailand Baht ',
			'USD' => 'US Dollar',
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Encoding type
    |--------------------------------------------------------------------------
    |
    | Encoding type of supported type associated with name
	| 
    */
	'encoding_type' => 'UTF-8',
	'lang' => [
		'ISO-8859-1' => 'English',
		'UTF-8' => 'Unicode',
		'GB2312' => 'Chinese Simplified',
		'GD18030' => 'Chinese Simplified',
		'BIG5' => 'Chinese Traditional',
	],

	/*
    |--------------------------------------------------------------------------
    | Transaction amount for test payment
    |--------------------------------------------------------------------------
    |
    | Amount for the respective currency code.
	| 
    */
	'transaction_amount' => [
		'MYR' => 1.00,
		'USD' => 1.00,
		'AUD' => 1.00,
		'CAD' => 1.00,
		'EUR' => 1.00,
		'GBP' => 1.00,
		'SGD' => 1.00,
		'HKD' => 2.50,
		'IDR' => 3000.00,
		'INR' => 15.00,
		'PHP' => 15.00,
		'THB' => 15.00,
		'TWD' => 15.00,
	],

	/*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    |
    | Description of each error 
	| 
    */
	'error_description' => [
		'Duplicate reference number' => 'Reference number must be unique for each transaction.',
		'Invalid merchant' => 'The merchant code does not exist.',
		'Invalid parameters' => 'Some parameter posted to iPay88 is invalid or empty',
		'Overlimit per transaction' => 'You exceed the amount value per transaction. * For Testing account, only amount RM 1.00 is allowed. ',
		'Payment not allowed ' => 'The Payment method you requested is not allowed for this merchant code, please contact ipay88 to enable your payment option.',
		'Permission not allow ' => 'Referrer URL in for your account registered in Ipay88 does not match. Please register your request and response URL with iPay88. ',
		'Signature not match' => 'The Signature generated is incorrect.',
		'Status not approved' => 'Account was suspended or not active.',
	],
];
