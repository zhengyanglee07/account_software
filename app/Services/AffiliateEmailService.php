<?php

namespace App\Services;

class AffiliateEmailService
{
    public static function getUrl($path, $parameters = [])
    {

        $urlHeader = app()->environment('local') ? 'http://' : 'https://';
        $domain = $urlHeader . $_SERVER['HTTP_HOST'];

        if (!empty($_SERVER['HTTP_ORIGIN'])) $domain = $_SERVER['HTTP_ORIGIN'];

        $queryParam =  [];
        foreach ($parameters as $key => $value) {
            $queryParam[] = urlencode($key) . '=' . urlencode($value);
        }
        $queryParamStr = '?' . implode('&', $queryParam);

        return $domain . $path . $queryParamStr;
    }
}
