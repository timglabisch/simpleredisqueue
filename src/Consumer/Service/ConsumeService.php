<?php

namespace Tg\RedisQueue\Consumer\Service;

use Tg\RedisQueue\Consumer\ConsumerContext;

class ConsumeService
{
    /** @var \Redis */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getJobsFromWorkingQueue(ConsumerContext $consumerContext)
    {
        return $this->redis->lRange($consumerContext->getWorkQueue(), 0, 1);
    }

    public function getJobs(ConsumerContext $consumerContext)
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

        return $jobs;

    }

    public function commitWorkQueue(ConsumerContext $consumerContext)
    {
        $this->redis->del($consumerContext->getWorkQueue());
    }

}