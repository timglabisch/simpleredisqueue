<?php

namespace Tg\RedisQueue\Redis;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Redis\Exception\CouldNotAcquireLockException;
use Tg\RedisQueue\Redis\Key\Key;

class LockHandler
{
    private $redis;

    private $logger;

    public function __construct(\Redis $redis, LoggerInterface $logger)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function createLockKey(string $string)
    {
        return new Key($string);
    }

    public function doInLock($string, int $timeout, callable $cb)
    {
        $lockKey = $this->createLockKey('lock:' . $string);

        if (!$this->acquire($lockKey, $timeout)) {
            $this->logger->info('could not acquire lock '.$lockKey);
            throw new CouldNotAcquireLockException();
        }

        $this->logger->debug('acquired lock '.$lockKey);

        try {
            $res = $cb();
        } finally {
            $this->release($lockKey);
            $this->logger->debug('released lock '.$lockKey);
        }

        return $res;
    }

    private function acquire(Key $key, $seconds = 60): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("PEXPIRE", KEYS[1], ARGV[2])
            else
                return redis.call("set", KEYS[1], ARGV[1], "NX", "PX", ARGV[2])
            end
        ';

        return (bool)$this->redis->eval($script, [(string)$key], [$key->getToken(), $seconds * 1000]);
    }

    private function release(Key $key)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        $this->redis->eval($script, [(string)$key], [$key->getToken()]);
    }
}