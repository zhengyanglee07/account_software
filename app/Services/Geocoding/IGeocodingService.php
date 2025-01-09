<?php

namespace App\Services\Geocoding;

/**
 * @deprecated
 *
 * Interface IGeocodingService
 * @package App\Services\Geocoding
 */
interface IGeocodingService
{
    /**
     * Should return an array containing ['lat' => ..., 'lng' => ...]
     * of the $location provided with any geocoding API
     *
     * @param array $location Contain 5-box address fields; street, city, state, postalCode, country
     * @return array
     */
    public function getLatLng(array $location): array;

    /**
     * batch version of the above getLatLng. Its implementation depends on
     * the support from API provider respectively.
     *
     * @param array $senderLocation Same as $location field in getLatLng
     * @param array $receiverLocation Same as $location field in getLatLng
     * @return array
     */
    public function getBatchLatLng(array $senderLocation, array $receiverLocation): array;

    /**
     * Get corresponding api key of geocoding API
     *
     * @return string|null
     */
    public function getKey(): ?string;
}