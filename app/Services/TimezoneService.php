<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    /**
     * Get a datetime string with the format of Y-m-d H:i:s and
     * convert it to Carbon object matching server's timezone.
     *
     * @param $datetime
     * @param $timezone
     * @return \Carbon\Carbon
     */
    public function convertDatetimeToServerTimezone($datetime, $timezone): Carbon
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, $timezone);
        $date->setTimezone('Asia/Kuala_Lumpur');

        return $date;
    }

    /**
     * Get a datetime string with the format of Y-m-d H:i:s and
     * convert it from server timezone datetime to Carbon object
     * matching provided timezone.
     *
     * @param $datetime
     * @param $timezone
     * @return \Carbon\Carbon
     */
    public function convertDatetimeBasedOnTimezone($datetime, $timezone): Carbon
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, 'Asia/Kuala_Lumpur');
        $date->setTimezone($timezone);

        return $date;
    }

    /**
     * TODO: update this validation, since it can't match exactly all
     *       of the supported timezones in PHP
     *
     * @param $tz
     * @return bool
     */
    public function isValidTimezoneId($tz): bool
    {
        $valid = array();
        $tza = timezone_abbreviations_list();

        foreach ($tza as $zone) {
            foreach ($zone as $item) {
                $valid[$item['timezone_id']] = true;
            }
        }

        unset($valid['']);
        return isset($valid[$tz]);
    }
}
