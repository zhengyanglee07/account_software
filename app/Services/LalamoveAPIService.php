<?php

namespace App\Services;

use App\Account;
use App\Location;
use App\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LalamoveAPIService
{
    private const SANDBOX_BASE_URL = 'https://rest.sandbox.lalamove.com';
    private const PRODUCTION_BASE_URL = 'https://rest.lalamove.com';

    /**
     * Supported service types (in hypershapes). For full list of services visit:
     * https://developers.lalamove.com/?plaintext--sandbox#service-types-malaysia
     */
    private const SUPPORTED_SERVICE_TYPES = ['MOTORCYCLE', 'CAR'];

    private string $domain;

    public function __construct()
    {
        $isProd = app()->environment('production');

        $this->domain = $isProd ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * Get quotation for lalamove service
     *
     * Refer to https://developers.lalamove.com/?plaintext--sandbox#get-quotation
     * for get quotations docs
     *
     * Mandatory fields:
     * - street
     * - city
     * - state
     * - zip
     * - country
     * - name (recipient name)
     * - phone (recipient phone)
     *
     * Optional fields:
     * - scheduleAt
     *
     * @param $accountId
     * @param $fields
     * @return array /v2/quotations response body
     * @throws \Exception|\Throwable
     * @throws HttpException
     */
    public function getQuotation($accountId, $fields): ?array
    {
        $senderLocation = $this->getSenderLocation($accountId);

        $reqBody = [
            'serviceType' => '', // Note: makeBatchPostRequest will fill up this field automatically
            'stops' => $this->makeStops(
                $senderLocation->displayAddr,

                // Note: ensure $fields contains every properties generateDisplayAddress() needs
                $this->generateDisplayAddress($fields)
            ),
            'deliveries' => $this->makeDeliveries($fields['name'], $fields['phone']),
            'requesterContact' => $this->makeRequesterContact($senderLocation),
            'specialRequests' => []
        ];

        // optional scheduleAt field
        if (isset($fields['scheduleAt'])) {
            $reqBody['scheduleAt'] = $fields['scheduleAt'];
        }

        try {
            $quotations = $this->makeBatchPostRequest(
                '/v2/quotations',
                $reqBody,
                $this->getLalamoveSettings($accountId)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in Lalamove /v2/quotations request', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'fields' => $fields
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in Lalamove get quotation', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'fields' => $fields
            ]);
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Unknown error in Lalamove get quotation', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'fields' => $fields
            ]);
            throw $e;
        }

        return $quotations;
    }

    /**
     * Place an order in Lalamove with previous quotation
     *
     * @param \App\LalamoveQuotation|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $lalamoveQuotation
     * @return array|mixed
     * @throws ClientException
     * @throws \JsonException
     * @throws HttpException
     */
    public function placeOrder($lalamoveQuotation)
    {
        if (!$lalamoveQuotation) {
            conflictAbort('Error in placing order: no Lalamove quotation found');
        }

        $reqBody = [
            // same with /v2/quotations
            'serviceType' => $lalamoveQuotation->service_type,
            'stops' => $lalamoveQuotation->stops,
            "deliveries" => $lalamoveQuotation->deliveries,
            'requesterContact' => preg_replace('/[^0-9]/','', $lalamoveQuotation->requester_contacts),
            'specialRequests' => $lalamoveQuotation->special_requests,

            // /v2/orders specific,
            'quotedTotalFee' => [
                'amount' => $lalamoveQuotation->total_fee_amount,
                'currency' => $lalamoveQuotation->total_fee_currency
            ],
            'sms' => false
        ];

        // optional scheduleAt field
        if ($lalamoveQuotation->scheduled_at) {
            $reqBody['scheduleAt'] = $lalamoveQuotation->scheduled_at;
        }

        try {
            $res = $this->makePostRequest(
                '/v2/orders',
                $reqBody,
                false,
                $this->getLalamoveSettings($lalamoveQuotation->order->account_id)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in Lalamove /v2/orders request', [
                'msg' => $e->getMessage(),
                'order_id' => $lalamoveQuotation->order->id
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in Lalamove get quotation', [
                'msg' => $e->getMessage(),
                'order' => $lalamoveQuotation->order
            ]);
            throw $e;
        }

        return json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get details of lalamove order
     *
     * For details please refer to:
     * https://developers.lalamove.com/?plaintext--sandbox#order-details
     *
     * @param string $orderRef orderRef returned by /v2/orders (or order_ref val in lalamove_delivery_orders table)
     * @param $accountId
     * @return array
     * @throws \JsonException
     */
    public function getOrderDetails(string $orderRef, $accountId): array
    {
        try {
            $res = $this->makeGetRequest(
                "/v2/orders/$orderRef",
                $this->getLalamoveSettings($accountId)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in Lalamove /v2/orders/{id} request', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in Lalamove get order details', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef
            ]);
            throw $e;
        }

        return json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get driver details of a lalamove order
     *
     * For details please refer to:
     * https://developers.lalamove.com/?plaintext--sandbox#driver-details
     *
     * @param string $orderRef orderRef returned by /v2/orders (or order_ref col in lalamove_delivery_orders)
     * @param string $driverId driverId from /orders/{id} response (or driver_id col in lalamove_delivery_order_details)
     * @param $accountId
     * @return array
     * @throws \JsonException
     */
    public function getDriverDetails(string $orderRef, string $driverId, $accountId): array
    {
        try {
            $res = $this->makeGetRequest(
                "/v2/orders/$orderRef/drivers/$driverId",
                $this->getLalamoveSettings($accountId)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in Lalamove /v2/orders/{orderId}/drivers/{driverId} request', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef,
                'driver_id' => $driverId
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in Lalamove get driver details', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef,
                'driver_id' => $driverId
            ]);
            throw $e;
        }

        return json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get driver location for a lalamove order.
     *
     * For details please refer to:
     * https://developers.lalamove.com/?plaintext--sandbox#driver-location
     *
     * @param string $orderRef orderRef returned by /v2/orders (or order_ref col in lalamove_delivery_orders)
     * @param string $driverId driverId from /orders/{id} response (or driver_id col in lalamove_delivery_order_details)
     * @param $accountId
     * @return array
     * @throws \JsonException
     */
    public function getDriverLocation(string $orderRef, string $driverId, $accountId): array
    {
        try {
            $res = $this->makeGetRequest(
                "/v2/orders/$orderRef/drivers/$driverId/location",
                $this->getLalamoveSettings($accountId)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in Lalamove /v2/orders/{orderId}/drivers/{driverId}/location request', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef,
                'driver_id' => $driverId
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in Lalamove get driver location', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef,
                'driver_id' => $driverId
            ]);
            throw $e;
        }

        return json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Cancel a lalamove order. Note that this cancellation has a chance to fail
     *
     * Please refer to lalamove cancellation policy at
     * https://developers.lalamove.com/?plaintext--sandbox#order-flow-user-cancellation
     *
     * Attempts to cancel an order that does not comply with lalamove cancellation
     * policy will get ERR_CANCELLATION_FORBIDDEN as response.
     *
     * @param string $orderRef orderRef returned by /v2/orders (or order_ref col in lalamove_delivery_orders)
     * @param $accountId
     * @return mixed
     * @throws ClientException
     * @throws \Exception
     * @throws \JsonException
     */
    public function cancelOrder(string $orderRef, $accountId)
    {
        try {
            $res = $this->makePutRequest(
                "/v2/orders/$orderRef/cancel",
                $this->getLalamoveSettings($accountId)
            );
        } catch (ClientException $e) {
            \Log::error('Something wrong in cancelling Lalamove order', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unknown error in cancelling Lalamove order', [
                'msg' => $e->getMessage(),
                'account_id' => $accountId,
                'lalamove_order_ref' => $orderRef,
            ]);
            throw $e;
        }

        return json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Generate simple display address for Lalamove API from $location
     *
     * $location array consists following fields:
     * - street
     * - city
     * - zip
     * - state
     * - country
     *
     * @param array $location
     * @return string
     */
    public function generateDisplayAddress(array $location): string
    {
        return $location['street']
            . ' ' . $location['city']
            . ' ' . $location['zip']
            . ' ' . $location['state']
            . ' ' . $location['country'];
    }

    /**
     * Make 'stops' property for Lalamove quotations and orders API
     *
     * @param string $senderDisplayAddr
     * @param string $receiverDisplayAddr
     * @return \string[][][][]
     */
    public function makeStops(string $senderDisplayAddr, string $receiverDisplayAddr): array
    {
        return [
            // sender stop
            [
                'addresses' => [
                    'ms_MY' => [
                        'displayString' => $senderDisplayAddr,
                        'country' => 'MY_KUL'
                    ]
                ]
            ],

            // receiver 1 stop
            [
                'addresses' => [
                    'ms_MY' => [
                        'displayString' => $receiverDisplayAddr,
                        'country' => 'MY_KUL'
                    ]
                ]
            ]
        ];
    }

    public function makeDeliveries($receiverName, $receiverPhone, $remarks = ''): array
    {
        return [
            [
                'toStop' => 1,
                'toContact' => [
                    'name' => $receiverName,
                    'phone' => $receiverPhone // TODO: validate phone number using lalamove validation later
                ],
                'remarks' => $remarks
            ]
        ];
    }

    /**
     * @param Location $location Sender location
     * @return array
     */
    public function makeRequesterContact(Location $location): array
    {
        return [
            'name' => $location->name,
            'phone' => $location->phone_number
        ];
    }

    /**
     * Make batch request of multiple services to lalamove API
     *
     * $configs fields (mostly from lalamove settings):
     * - apiSecret
     * - apiKey
     * - enableCar
     * - enableBike
     *
     * @param string $path
     * @param array $body
     * @param array $configs
     * @return mixed
     * @throws \JsonException
     * @throws \Exception
     * @throws \Throwable
     */
    private function makeBatchPostRequest(
        string $path,
        array $body,
        array $configs
    )
    {
        $promises = [];

        foreach (self::SUPPORTED_SERVICE_TYPES as $SERVICE_TYPE) {
            // skip corresponding req if motorcycle/car is disabled by user
            if ($SERVICE_TYPE === 'MOTORCYCLE' && !$configs['enableBike']) continue;
            if ($SERVICE_TYPE === 'CAR' && !$configs['enableCar']) continue;

            $body['serviceType'] = $SERVICE_TYPE;

            $promises[$SERVICE_TYPE] = $this->makePostRequest(
                $path,
                $body,
                true,
                $configs
            );
        }

        // Wait for the requests to complete; throws a ConnectException
        // if any of the requests fail
        $responses = Promise\Utils::unwrap($promises);

        return array_map(
            static fn($res) => json_decode($res->getBody(), true, 512, JSON_THROW_ON_ERROR),
            $responses
        );
    }


    /**
     * Post reqs primarily used in quotations and place order
     *
     * @param string $path
     * @param array $body
     * @param bool $isAsync
     * @param array $configs
     * @return Promise\PromiseInterface|ResponseInterface
     * @throws \JsonException
     * @throws \Exception
     */
    private function makePostRequest(
        string $path,
        array $body,
        bool $isAsync,
        array $configs
    )
    {
        $client = new Client(['base_uri' => $this->domain]);

        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature(
            $configs['apiSecret'],
            $timestamp,
            'POST',
            $path,
            $body
        );
        $headers = $this->generateHeaders(
            $configs['apiKey'],
            $timestamp,
            $signature
        );
        $options = [
            'headers' => $headers,
            'json' => $body
        ];
        return $isAsync
            ? $client->postAsync($path, $options)
            : $client->post($path, $options);
    }

    /**
     * GET reqs primarily used in order details, driver details and driver location
     *
     * @param string $path
     * @param array $configs
     * @return ResponseInterface
     * @throws \JsonException
     * @throws \Exception
     */
    private function makeGetRequest(
        string $path,
        array $configs
    ): ResponseInterface
    {
        $client = new Client(['base_uri' => $this->domain]);

        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature(
            $configs['apiSecret'],
            $timestamp,
            'GET',
            $path
        );
        $headers = $this->generateHeaders(
            $configs['apiKey'],
            $timestamp,
            $signature
        );
        $options = [
            'headers' => $headers
        ];

        return $client->get($path, $options);
    }

    /**
     * PUT reqs primarily used in cancel order
     *
     * @param string $path
     * @param array $configs
     * @param array $body
     * @return ResponseInterface
     * @throws \JsonException
     */
    private function makePutRequest(
        string $path,
        array $configs,
        array $body = []
    ): ResponseInterface
    {
        $client = new Client(['base_uri' => $this->domain]);

        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature(
            $configs['apiSecret'],
            $timestamp,
            'PUT',
            $path,
            $body
        );
        $headers = $this->generateHeaders(
            $configs['apiKey'],
            $timestamp,
            $signature
        );
        $options = [
            'headers' => $headers,
            'json' => $body
        ];
        return $client->put($path, $options);
    }

    /**
     * Generate unix timestamp in milliseconds to be used in API
     *
     * @return int
     */
    private function generateTimestamp(): int
    {
        return (int)(microtime(true) * 1000);
    }

    /**
     * Generate signature per API call according to Lalamove requirement
     * ref: https://developers.lalamove.com/?plaintext--sandbox#introduction-authentication
     *
     * @param string $secret Lalamove API secret
     * @param int $timestamp Unix timestamp (in milliseconds)
     * @param string $method HTTP method in CAPS
     * @param string $path Lalamove API endpoint
     * @param array|null $body Request body (in array form). null if not present
     * @return string
     * @throws \JsonException
     */
    public function generateSignature(
        string $secret,
        int $timestamp,
        string $method,
        string $path,
        ?array $body = null
    ): string 
    {
        $encodedBody = is_null($body)
            ? ''
            : json_encode($body, JSON_THROW_ON_ERROR);
        $rawSignature = "$timestamp\r\n$method\r\n$path\r\n\r\n$encodedBody";

        return hash_hmac('sha256', $rawSignature, $secret);
    }

    /**
     * @param string $apiKey Lalamove API key
     * @param int $timestamp Unix timestamp (in milliseconds)
     * @param string $sig Signature generated by using generateSignature
     * @param string $countryCode
     * @return array
     * @throws \Exception
     */
    public function generateHeaders(string $apiKey, int $timestamp, string $sig, string $countryCode = 'MY_KUL'): array
    {
        $token = "$apiKey:$timestamp:$sig";

        return [
            'Content-Type' => 'application/json',
            'Authorization' => "hmac $token",
            'X-LLM-Country' => $countryCode,
            'X-Request-ID' => bin2hex(random_bytes(32))
        ];
    }

    private function getLalamoveSettings($accountId): array
    {
        $lalamoveSettings = Account::find($accountId)->lalamove ?? null;

        return !$lalamoveSettings
            ? []
            : [
                'apiKey' => $lalamoveSettings->api_key,
                'apiSecret' => $lalamoveSettings->api_secret,
                'enableCar' => $lalamoveSettings->enable_car,
                'enableBike' => $lalamoveSettings->enable_bike
            ];
    }

    /**
     * Get sender location for $accountId. Throws HTTP Exception
     * if certain criteria not met
     *
     * @param $accountId
     * @return Location|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws HttpException
     */
    private function getSenderLocation($accountId)
    {
        $senderLocation = Location::ignoreAccountIdScope()->where('account_id', $accountId)->first();
        $senderLocation['phone_number'] = preg_replace('/[^0-9]/','', $senderLocation['phone_number']);

        if (!$senderLocation) {
            conflictAbort('Sender location not set');
        }

        // Lalamove Malaysia support only Malaysia. For another country need to request
        // foreign API key & secret
        if (strtolower($senderCountry = $senderLocation->country) !== 'malaysia') {
            conflictAbort("Sender country $senderCountry is not supported by Lalamove Malaysia");
        }

        return $senderLocation;
    }
}
