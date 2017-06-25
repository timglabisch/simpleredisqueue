<?php

namespace Tg\RedisQueue\Consumer\Runtime;


use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;

interface ConsumerRuntimeInterface
{

    public function run(array $jobs, IsolatedConsumerInterface $isolatedConsumer);

}