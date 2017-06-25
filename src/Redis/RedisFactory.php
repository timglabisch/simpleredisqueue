<?php

namespace Tg\RedisQueue\Redis;

class RedisFactory
{

    public static function create(
        $host,
        $port
    ): \Redis {

        $redis = new \Redis();
        $redis->connect($host, $port);
        $redis->ping();

        return $redis;

    }

}