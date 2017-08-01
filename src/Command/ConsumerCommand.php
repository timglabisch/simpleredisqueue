<?php

namespace Tg\RedisQueue\Command;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\Runtime\ConsumerRuntimeInterface;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;
use Tg\RedisQueue\Consumer\Service\ConsumerStatusService;
use Tg\RedisQueue\Consumer\Status\StatusStarted;
use Tg\RedisQueue\Service\ConsumeService;

class ConsumerCommand
{

    /** @var ConsumeService */
    private $consumeService;

    /** @var ConsumerStatusService */
    private $consumerStatusService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ConsumeService $consumeService,
        ConsumerStatusService $consumerStatusService,
        LoggerInterface $logger
    ) {
        $this->consumeService = $consumeService;
        $this->consumerStatusService = $consumerStatusService;
        $this->logger = $logger;
    }


    public function execute(
        ConsumerRuntimeInterface $runtime,
        ConsumerContext $consumerContext,
        IsolatedConsumerInterface $consumer
    ) {

        $this->consumerStatusService->addStatus(new StatusStarted(
            'worker:'.$consumerContext->getConsumerNum(),
            $consumerContext->getQueue(),
            time(),
            $consumerContext->getWorkQueue()
        ));

        $uncommitedJobs = $this->consumeService->getJobsFromWorkingQueue($consumerContext);

        if ($uncommitedJobs) {
            $this->logger->warning("Found Uncommited Jobs, start to recover.");

            //$this->consumerStatusService->addStatus($consumerContext, 'work on uncommited jobs');
            $this->processJobs($uncommitedJobs, $runtime, $consumerContext, $consumer);
        }

        while (true) {

            $this->logger->info("Start Waiting for Jobs");

            $jobs = $this->consumeService->getJobs($consumerContext);

            if (!$jobs) {
                $this->logger->info("no jobs to process");
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