<?php

namespace Tg\RedisQueue\Service;

use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Dto\Job;
use Tg\RedisQueue\Dto\EnqueuedJob;
use Tg\RedisQueue\Dto\EnqueuedJobInterface;

class ConsumeService
{
    /** @var \Redis */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /** @return EnqueuedJobInterface[] */
    public function getJobsFromWorkingQueue(ConsumerContext $consumerContext): array
    {
        $rawJobs = $this->redis->lRange($consumerContext->getWorkQueue(), 0, 1);

        return array_map(
            function (string $data) {
                return EnqueuedJob::newFromDecode($data);
            },
            $rawJobs
        );
    }

    /** @return EnqueuedJobInterface[] */
    public function getJobs(ConsumerContext $consumerContext): array
    {

        $jobs = [];

        $elapsedTime = 0;

        while (true) {
            $job = $this->redis->brpoplpush($consumerContext->getQueue(), $consumerContext->getWorkQueue(), $consumerContext->getTickTimeout());

            if ($job) {
                $jobs[] = $job;
            }

            $elapsedTime += $consumerContext->getTickTimeout();

            if (count($jobs) >= $consumerContext->getMaxJobsToDequeue()) {
                break;
            }

            if ($elapsedTime >= $consumerContext->getTimeout()) {
                break;
            }

            echo "time left to collect jobs\n";

            //$output->writeln("time left to collect jobs");
            continue;
        }

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