<?php

namespace App\Services\Geocoding;

use GuzzleHttp\Client;

/**
 * @deprecated
 *
 * Docs:
 * https://developer.mapquest.com/documentation/geocoding-api/address/post/
 *
 * Location:
 * https://developer.mapquest.com/documentation/common/forming-locations/#advancedLocations
 *
 * Class MapquestService
 */
class MapquestService implements IGeocodingService
{
    private const RESOURCE_URL = 'http://www.mapquestapi.com/geocoding/v1/address';
    private const BATCH_RESOURCE_URL = 'http://www.mapquestapi.com/geocoding/v1/batch';

    public function getLatLng(array $location): array
    {
        $client = new Client();

        $req = $client->post($this->getUrlWithKey(), [
            'json' => [
                'location' => $location,
                'options' => [
                    'maxResults' => 5
                ]
            ]
        ]);

        return json_decode($req->getBody(), true)['results'][0]['locations'][0]['latLng'] ?? [];
    }

    public function getBatchLatLng(array $senderLocation, array $receiverLocation): array
    {
        $client = new Client();

        $req = $client->post($this->getUrlWithKey(true), [
            'json' => [
                'locations' => [
                    $senderLocation,
                    $receiverLocation
                ],
                'options' => [
                    'maxResults' => 5
                ]
            ]
        ]);

        return array_map(
            static fn($c) => $c['locations'][0]['latLng'] ?? [],
            json_decode($req->getBody(), true)['results'] ?? []
        );
    }

    private function getUrlWithKey(bool $batch = false): string
    {
        return ($batch ? self::BATCH_RESOURCE_URL : self::RESOURCE_URL) . '?key=' . $this->getKey();
    }

    public function getKey(): ?string
    {
        return config('mapquest.customer_key');
    }
}