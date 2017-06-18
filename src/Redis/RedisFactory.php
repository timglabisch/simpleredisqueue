<?php

namespace Tg\RedisQueue\Redis;

class RedisFactory
{

    public static function create(): \Redis {

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->ping();

        return $redis;

    }

}