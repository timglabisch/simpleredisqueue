<?php

namespace Tg\RedisQueue\Consumer\Service;

use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\Status\CodecException;
use Tg\RedisQueue\Consumer\Status\StatusCodecInterface;
use Tg\RedisQueue\Consumer\Status\StatusInterface;
use Tg\RedisQueue\Consumer\Status\StatusStartedCodec;
use Tg\RedisQueue\Dto\EnqueuedJob;

class QueueStatusService
{

    /** @var \Redis */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /** @return int */
    public function getCountJobsInQueue(string $queue)
    {
        return $this->redis->lLen($queue);
    }

    /** @return EnqueuedJob[] */
    public function getJobsInQueue(string $queue)
    {
        $rawJobs = $this->redis->lRange($queue, 0, -1);

        return array_map(
            function (string $data) {
                return EnqueuedJob::newFromDecode($data);
            },
            $rawJobs
        );
    }

    public function moveQueueToQueue(string $fromQueue, string $targetQueue)
    {
        while($this->redis->rpoplpush($fromQueue, $targetQueue));
    }

    public function deleteQueue(string $queue)
    {
        $this->redis->del($queue);
    }

    public function deleteJob(string $queue, string $jobId)
    {
        $jobs = $this->getJobsInQueue($queue);

        foreach ($jobs as $job) {
            if ($job->getJobId() != $jobId) {
                continue;
            }

            return !!$this->redis->lRem($queue, $job->encode(), 1);
        }

        return false;
    }

}