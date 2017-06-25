<?php

namespace Tg\RedisQueue\Command;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\Runtime\ConsumerRuntimeInterface;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;
use Tg\RedisQueue\Service\ConsumeService;

class ConsumerCommand
{

    /** @var ConsumerRuntimeInterface */
    private $runtime;

    /** @var ConsumeService */
    private $consumeService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ConsumeService $consumeService,
        LoggerInterface $logger
    ) {
        $this->consumeService = $consumeService;
        $this->logger = $logger;
    }


    public function execute(
        ConsumerRuntimeInterface $runtime,
        ConsumerContext $consumerContext,
        IsolatedConsumerInterface $consumer
    ) {
        $uncommitedJobs = $this->consumeService->getJobsFromWorkingQueue($consumerContext);

        if ($uncommitedJobs) {
            $this->logger->warning("Found Uncommited Jobs, start to recover.");

            $this->processJobs($uncommitedJobs, $runtime, $consumerContext, $consumer);
        }

        while (true) {

            $this->logger->info("Start Waiting for Jobs");

            $jobs = $this->consumeService->getJobs($consumerContext);

            if (!$jobs) {
                $this->logger->info("no jobs pro process");
                continue;
            }

            $this->processJobs($jobs, $runtime, $consumerContext, $consumer);
        }

        $this->logger->info("Finish");
    }

    private function processJobs(
        array $jobs,
        ConsumerRuntimeInterface $runtime,
        ConsumerContext $consumerContext,
        IsolatedConsumerInterface $isolatedConsumer
    ) {
        $this->logger->info(sprintf("collected %d jobs", count($jobs)));

        if (!count($jobs)) {
            $this->logger->info(sprintf("no jobs."));
        }

        $this->logger->info("Start working on jobs");

        $runtime->run($jobs, $isolatedConsumer);

        $this->logger->info("Commit Work");
        $this->consumeService->commitWorkQueue($consumerContext);

        $this->logger->info("Finish Work");
    }

}