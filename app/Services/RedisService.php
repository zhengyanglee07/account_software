<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisService
{
    public const REDIS_EXPIRED_TIME_IN_SECONDS = 259200; // 3 days

    public static function getId()
    {
        if (!empty(request()->cookie('cid')))
            return request()->cookie('cid');
        if (!empty(request()->header('x-client-id')))
            return request()->header('x-client-id');
    }

    public static function set($key, $value, $deleteIfNull = false, $skipIfNull = false)
    {
        if (gettype($value) === 'array') $value = json_encode($value);
        if (!isset($value)) {
            if ($skipIfNull) return;
            if ($deleteIfNull) self::delete($key);
            return;
        };

        Redis::set(self::getId() . "_$key", $value, 'EX', self::REDIS_EXPIRED_TIME_IN_SECONDS);
    }

    public static function get($key, $isCustom = false)
    {
        $key = $isCustom ? $key : self::getId() . "_$key";
        $value = Redis::get($key);
        $decodedValue = json_decode($value, true);

        // Return original value if value not decodable
        if (JSON_ERROR_NONE !== json_last_error() || !is_array($decodedValue))
            return $value;

        return $decodedValue;
    }

    public static function append($key, $property, $value, $index = null)
    {
        $newValue = self::get($key);

        if (is_int($index)) $newValue[$index][$property] = $value;
        else $newValue[$property] = $value;

        self::set($key, $newValue);
    }

    public static function delete($key, $isCustom = false)
    {
        Redis::del($isCustom ? $key : (self::getId() . "_$key"));
    }

    public static function getAllKeys($sessionId = null)
    {
        return Redis::keys(($sessionId ?: self::getId()) . '_*');
    }

    public static function getAllValues()
    {
        return array_map(function ($key) {
            return self::get(str_replace(config('database.redis.options.prefix'), '', $key), true);
        }, self::getAllKeys());
    }

    public static function deleteAll(array $except = [])
    {
        foreach (self::getAllKeys() as $key) {
            $redisKey = str_replace(config('database.redis.options.prefix'), '', $key);
            if (in_array(explode('_', $redisKey)[1] ?? '', $except)) continue;
            self::delete($redisKey, true);
        }
    }
    public static function renameKey($oldSessionId)
    {
        $keysToReplace = self::getAllKeys($oldSessionId);
        foreach ($keysToReplace as $key) {
            $oldKey = str_replace(config('database.redis.options.prefix'), '', $key);
            $explodedKey = explode('_', $oldKey);
            $newKey = self::getId() . '_' . $explodedKey[1];
            try {
                Redis::rename($oldKey, $newKey);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    // Combine client data as one key value
    // public static function set($key, $value)
    // {
    // 	$checkoutData = json_decode(Redis::get(session()->getId() . '_checkout') ?? '[]', true);
    // 	if (gettype($value) === 'array') $value = json_encode($value);
    // 	$checkoutData[$key] = $value;
    // 	Redis::set(session()->getId() . '_checkout', json_encode($checkoutData));
    // }

    // public static function get($key)
    // {
    // 	$value = Redis::get(session()->getId() . "__checkout");
    // 	$decodedData = json_decode($value, true);
    // 	return optional($decodedData)[$key];
    // }
}
