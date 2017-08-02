<?php

namespace Tg\RedisQueue\Service;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Dto\EnqueuedJob;
use Tg\RedisQueue\Dto\EnqueuedJobInterface;
use Tg\RedisQueue\Redis\Exception\CouldNotAcquireLockException;
use Tg\RedisQueue\Redis\LockHandler;

class ConsumeService
{
    /** @var \Redis */
    private $redis;

    /** @var LockHandler */
    private $lockHandler;

    public function __construct(
        \Redis $redis,
        LockHandler $lockHandler
    )
    {
        $this->redis = $redis;
        $this->lockHandler = $lockHandler;
    }

    /** @return EnqueuedJobInterface[] */
    public function getJobsFromWorkingQueue(ConsumerContext $consumerContext): array
    {
        $rawJobs = $this->lockHandler->doInLock($consumerContext->getWorkQueue(), 60, function() use ($consumerContext) {
            $this->redis->lRange($consumerContext->getWorkQueue(), 0, -1);
        });

        return array_map(
            function (string $data) {
                return EnqueuedJob::newFromDecode($data);
            },
            $rawJobs
        );
    }

    /**
     * @param ConsumerContext $consumerContext
     * @param LoggerInterface $logger
     * @throws CouldNotAcquireLockException
     * @return EnqueuedJobInterface[]
     */
    public function getJobs(ConsumerContext $consumerContext, LoggerInterface $logger): array
    {

        $jobs = $this->lockHandler->doInLock(
            $consumerContext->getWorkQueue(),
            $consumerContext->getTimeout() + 10,
            function() use ($consumerContext, $logger) {
                $jobs = [];

                $startTime = microtime(true);

                while (true) {
                    $job = $this->redis->brpoplpush($consumerContext->getQueue(), $consumerContext->getWorkQueue(), $consumerContext->getTickTimeout());

                    if ($job) {
                        $jobs[] = $job;
                    }

                    if (count($jobs) >= $consumerContext->getMaxJobsToDequeue()) {
                        break;
                    }

                    $elapsedTime = microtime(true) - $startTime;
                    if ($elapsedTime >= $consumerContext->getTimeout()) {
                        break;
                    }

                    $logger->info("time left to collect jobs");

                    continue;
                }

                return $jobs;
            }
        );

        return array_map(
            function (string $data) {
                return EnqueuedJob::newFromDecode($data);
            },
            $jobs
        );
    }

    public function commitWorkQueue(ConsumerContext $consumerContext)
    {
        $this->redis->del($consumerContext->getWorkQueue());
    }

}