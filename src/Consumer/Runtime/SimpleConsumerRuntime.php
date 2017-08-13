<?php

namespace Tg\RedisQueue\Consumer\Runtime;


use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\Runtime\ConsumerRuntimeInterface;
use Tg\RedisQueue\Consumer\IsolatedConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;
use Tg\RedisQueue\Service\JobEnqueueService;
use Tg\RedisQueue\Service\StatusService;

class SimpleConsumerRuntime implements ConsumerRuntimeInterface
{
    /** @var JobEnqueueService */
    private $enqueueService;

    /** @var StatusService */
    private $statusService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        JobEnqueueService $enqueueService,
        StatusService $statusService,
        LoggerInterface $logger
    ) {
        $this->enqueueService = $enqueueService;
        $this->statusService = $statusService;
        $this->logger = $logger;
    }

    public function isReady(): bool
    {
        return true;
    }

    public function run(array $jobs, ConsumerContext $context, IsolatedConsumerInterface $isolatedConsumer, callable $commit)
    {
        $isolatedConsumerContext = new IsolatedConsumerContext(
            $this->enqueueService,
            $this->statusService,
            $this->logger
        );

        $isolatedConsumer->handle($jobs, $isolatedConsumerContext);

        $commit();
    }

}