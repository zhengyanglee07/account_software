<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('get_ip_address')) {
    /**
     * Get proper IP address of client
     *
     * @param boolean $deep_detect
     *   => get more accurate IP address of client
     * @return string
     */
    function get_ip_address($deep_detect = TRUE)
    {
        // set as IP address of Kuala Lumpur, Malaysia for local env
        if (app()->environment() === 'local' || app()->environment() === 'testing')
            return '210.48.222.13';
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            //to check ip is pass from proxy
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            //check ip from share internet
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        return $ip;
    }
}

if (!function_exists('ip_info')) {
    /**
     * Get client info (location, currency & timezone) by IP
     *
     * @param string $ip
     *   => get client info of specific IP
     * @return array
     */
    function ip_info($ip = null)
    {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = get_ip_address();
        }
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                $address = array($ipdat->geoplugin_countryName);
                if (@strlen($ipdat->geoplugin_regionName) >= 1)
                    $address[] = $ipdat->geoplugin_regionName;
                if (@strlen($ipdat->geoplugin_city) >= 1)
                    $address[] = $ipdat->geoplugin_city;

                $output = [
                    'location' => [
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$ipdat->geoplugin_continentName,
                        "continent_code" => @$ipdat->geoplugin_continentCode,
                        "address"        => implode(", ", array_reverse($address)),
                    ],
                    'currency' =>  [
                        'code'        => @$ipdat->geoplugin_currencyCode,
                        'symbol'      => @$ipdat->geoplugin_currencySymbol,
                        'symbol_utf8' => @$ipdat->geoplugin_currencySymbol_UTF8,
                    ],
                    'timezone' => @$ipdat->geoplugin_timezone,
                ];
            }
        }
        return $output;
    }
}
