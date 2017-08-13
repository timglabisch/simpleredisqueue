<?php

namespace Tg\RedisQueue\Consumer\Runtime;


use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;

interface ConsumerRuntimeInterface
{

    public function isReady(): bool;

    public function run(array $jobs, ConsumerContext $context, IsolatedConsumerInterface $isolatedConsumer, callable $commit);

}