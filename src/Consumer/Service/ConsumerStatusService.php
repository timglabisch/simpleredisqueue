<?php

namespace Tg\RedisQueue\Consumer\Service;

use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\Status\CodecException;
use Tg\RedisQueue\Consumer\Status\StatusCodecInterface;
use Tg\RedisQueue\Consumer\Status\StatusInterface;
use Tg\RedisQueue\Consumer\Status\StatusStartedCodec;

class ConsumerStatusService
{

    /** @var \Redis */
    private $redis;

    private $key;

    private static $ttl = 45;

    /** @var StatusCodecInterface[] */
    private $statusCodecs = [];

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
        $this->key = base64_encode(random_bytes(32));
        $this->statusCodecs = [
            new StatusStartedCodec()
        ];
    }

    public function addWorkerStatus(ConsumerContext $context, $status, $payload)
    {
        if (strpos($status, '|') !== false) {
            throw new \InvalidArgumentException('status must not contain "|"');
        }

        $payload = $payload ? json_encode($payload) : '';

        $this->redis->expire('consumerlog:' . $context->getConsumerNum(), 3600);
        $this->redis->zRemRangeByScore('consumerlog', 0, time() - 600);
        $this->redis->zAdd('consumerlog:' . $context->getConsumerNum(), time(), 'worker:' . $context->getConsumerNum() . '|' . time() . '|' . $status . '|' . $payload);
    }

    public function addStatus(StatusInterface $status)
    {
        foreach ($this->statusCodecs as $codec) {
            if (!$codec->supportsEncode($status)) {
                continue;
            }

            $this->redis->zRemRangeByScore('consumerlog', 0, time() - 4000); // drop old entries.
            $this->redis->zAdd('consumerlog', time(), $codec->encode($status));
            return;
        }

        throw new \LogicException("could not find codec.");

    }

    /**
     * @return \Generator|StatusInterface[]
     */
    public function getStatus()
    {
        $entries = $this->redis->zRangeByScore('consumerlog', '-inf', '+inf');

        foreach ($entries as $entry) {
            foreach ($this->statusCodecs as $codec) {
                if (!$codec->supportsDecode($entry)) {
                    continue;
                }

                yield $codec->decode($entry);
            }
        }
    }

    /*
    public function acquireConsumerLock(ConsumerContext $context)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("PEXPIRE", KEYS[1], ARGV[2])
            else
                return redis.call("set", KEYS[1], ARGV[1], "NX", "PX", ARGV[2])
            end
        ';

        $res = $this->redis->eval($script, array($this->key, $this->key, self::$ttl), 1);

        $a = 0;
    }
    */

}