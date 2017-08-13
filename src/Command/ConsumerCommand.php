<?php

namespace Tg\RedisQueue\Command;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerInterface;
use Tg\RedisQueue\Consumer\Runtime\ConsumerRuntimeInterface;
use Tg\RedisQueue\Consumer\Service\ConsumerStatusService;
use Tg\RedisQueue\Consumer\Status\StatusStarted;
use Tg\RedisQueue\Lock\FilesystemLockHandler;
use Tg\RedisQueue\Service\ConsumeService;

class ConsumerCommand
{

    /** @var ConsumeService */
    private $consumeService;

    /** @var ConsumerStatusService */
    private $consumerStatusService;

    /** @var LoggerInterface */
    private $logger;

    /** @var FilesystemLockHandler */
    private $filesystemLockHandler;

    public function __construct(
        ConsumeService $consumeService,
        ConsumerStatusService $consumerStatusService,
        LoggerInterface $logger,
        FilesystemLockHandler $filesystemLockHandler
    ) {
        $this->consumeService = $consumeService;
        $this->consumerStatusService = $consumerStatusService;
        $this->logger = $logger;
        $this->filesystemLockHandler = $filesystemLockHandler;
    }

    public function execute(
        ConsumerRuntimeInterface $runtime,
        ConsumerContext $consumerContext,
        IsolatedConsumerInterface $consumer,
        $loop = true
    ) {
        return $this->filesystemLockHandler->doInLock(
            $consumerContext->getWorkQueue(),
            function () use ($runtime, $consumerContext, $consumer, $loop) {
                return $this->executeInLocalLock($runtime, $consumerContext, $consumer, $loop);
            }
        );

    }

    private function executeInLocalLock(
        ConsumerRuntimeInterface $runtime,
        ConsumerContext $consumerContext,
        IsolatedConsumerInterface $consumer,
        $loop = true
    ): int {

        $this->consumerStatusService->addStatus(
            new StatusStarted(
                'worker:' . $consumerContext->getConsumerNum(),
                $consumerContext->getQueue(),
                time(),
                $consumerContext->getWorkQueue()
            )
        );

        $uncommitedJobs = $this->consumeService->getJobsFromWorkingQueue($consumerContext);

        if ($uncommitedJobs) {
            $this->logger->warning("Found Uncommited Jobs, start to recover.");

            if (!$runtime->isReady()) {
                $this->logger->info("Runtime is not Ready, wait ...");
                usleep(100);
            }

            //$this->consumerStatusService->addStatus($consumerContext, 'work on uncommited jobs');
            $this->processJobs($uncommitedJobs, $runtime, $consumerContext, $consumer);
        }

        $jobCounter = 0;

        do {

            if (!$runtime->isReady()) {
                $this->logger->info("Runtime is not Ready, wait ...");
                usleep(100);
            }

            $this->logger->info("Start Waiting for Jobs");

            $jobs = $this->consumeService->getJobs($consumerContext, $this->logger);

            if (!$jobs) {
                $this->logger->info("no jobs to process");
                continue;
            }

            $jobCounter += count($jobs);

            $this->processJobs($jobs, $runtime, $consumerContext, $consumer);
        } while ($loop);

        $this->logger->info("Finish");

        return $jobCounter;
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

        $runtime->run(
            $jobs,
            $consumerContext,
            $isolatedConsumer,
            function () use ($consumerContext) {
                $this->logger->info("Commit Work");
                $this->consumeService->commitWorkQueue($consumerContext);

                $this->logger->info("Finish Work");
            }
        );
    }

}