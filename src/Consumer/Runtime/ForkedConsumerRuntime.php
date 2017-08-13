<?php

namespace Tg\RedisQueue\Consumer\Runtime;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;
use Tg\RedisQueue\Redis\RedisFactory;
use Tg\RedisQueue\Service\JobEnqueueService;
use Tg\RedisQueue\Service\Logger;
use Tg\RedisQueue\Service\ServiceContainer;
use Tg\RedisQueue\Service\StatusService;

class ForkedConsumerRuntime implements ConsumerRuntimeInterface
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
        $this->logger->info("Before Forking");
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->logger->error('Invalid Pid (-1)');
            die(1);
        }

        if ($pid === 0) {
            $this->logger->info("Forked Worker of ". posix_getppid());

            $exitCode = $this->runWorker($jobs, $context, $isolatedConsumer);
            die ($exitCode);
        }

        if (is_numeric($pid)) {
            $this->logger->info(sprintf('Wait for Worker "%s"', $pid));
            $exit = pcntl_waitpid(-1, $status);
            $this->logger->info(sprintf('Worker "%s" Stopped with exit Code "%s"', $exit, $status));
            $commit();
            $this->logger->info(sprintf('Worker "%s" Committed', $exit));
            return;
        }

        throw new \LogicException("Invalid Pid");

    }

    private function runWorker($jobs, ConsumerContext $context, IsolatedConsumerInterface $isolatedConsumer): int
    {
        // we create a complete new environment for the forked process.

        $workerContainer = new ServiceContainer(
            new Logger(),
            RedisFactory::create($context->getRedis(), $context->getRedisPort(), 8) // todo
        );

        $context = new IsolatedConsumerContext(
            $workerContainer->getJobEnqueueService(),
            $workerContainer->getStatusService(),
            $workerContainer->getLogger()
        );

        $isolatedConsumer->handle($jobs, $context);

        return $isolatedConsumer->handle($jobs, $context) == IsolatedConsumerInterface::RETURN_SUCCESS ? 0 : 1;
    }

}