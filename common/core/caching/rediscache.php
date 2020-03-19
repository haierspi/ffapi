<?php

namespace ff\caching;

use ff\nosql\redis;

class rediscache
{
    public static function get($key)
    {

        $data = redis::get($key);
        $ttl = redis::get($key . '_ttl');
        if (!$ttl || !$data || $ttl < TIMESTAMP) {
            return false;
        } else {
            return json_decode($data, 1);
        }
    }

    public static function set($key, $data, $ttl)
    {
        if (!$key || !$data || !$ttl) {
            return false;
        }
        redis::set($key, json_encode($data));
        redis::set($key . '_ttl', TIMESTAMP + $ttl);
        return true;
    }
}
