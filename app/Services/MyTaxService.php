<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MyTaxService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected const CACHE_KEY = 'myTax_access_token';

    public function __construct()
    {
        // Set your API credentials and base URL
        $this->clientId = config('my-tax.client_id');
        $this->clientSecret = config('my-tax.client_secret');
        $this->baseUrl = config('my-tax.base_url');
    }

    /**
     * Get the access token, renew if expired.
     */
    public function getAccessToken()
    {
        // Check if token exists and is valid
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        // Token is missing or expired, renew it
        // return $this->renewAccessToken();
    }

    /**
     * Renew the access token.
     */
    private function renewAccessToken()
    {

        $response = Http::asForm()->post($this->baseUrl . '/connect/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'InvoicingAPI'
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            $expiresIn = $response->json()['expires_in'];

            Cache::put(self::CACHE_KEY, $token, $expiresIn - 60);
            return $token;
        }

        if ($response->status() === 400) {
            $message = $response->json()['error'];
            throw new \Exception($message);
        }

        throw new \Exception('Failed to retrieve access token');
    }

    /**
     * Make an API call with the access token.
     */
    public function apiRequest($endpoint, $method = 'GET', $data = [])
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->$method("{$this->baseUrl}/$endpoint", $data);

        // If unauthorized, try renewing the token and retrying the request
        if ($response->status() === 401) {
            $this->renewAccessToken();
            $token = $this->getAccessToken();

            return Http::withToken($token)
                ->$method("{$this->baseUrl}/$endpoint", $data);
        }

        return $response;
    }
}
