<?php

namespace Tg\RedisQueue\Consumer;


use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Service\JobEnqueueService;
use Tg\RedisQueue\Service\StatusService;

class IsolatedConsumerContext
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

    public function getEnqueueService(): JobEnqueueService
    {
        return $this->enqueueService;
    }

    public function getStatusService(): StatusService
    {
        return $this->statusService;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}