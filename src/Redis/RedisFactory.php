<?php

namespace Tg\RedisQueue\Redis;

class RedisFactory
{

    public static function create(
        $host,
        $port,
        int $db
    ): \Redis {

        $redis = new \Redis();
        $redis->connect($host, $port);
        $redis->ping();
        $redis->select($db);

        return $redis;

    }

}