<?php

namespace Tg\RedisQueue\Consumer;


use Tg\RedisQueue\Dto\JobInterface;

interface IsolatedConsumerInterface
{
    const RETURN_FAILED = 0;
    const RETURN_SUCCESS = 1;

    /**
     * @param JobInterface[] $jobs
     * @param IsolatedConsumerContext $context
     * @return mixed
     */
    public function handle(array $jobs, IsolatedConsumerContext $context);
}