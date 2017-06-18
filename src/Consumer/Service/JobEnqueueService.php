<?php

namespace Tg\RedisQueue\Consumer\Service;


use Tg\RedisQueue\JobInterface;
use Tg\RedisQueue\TrackedJob;
use Tg\RedisQueue\TrackedJobInterface;

class JobEnqueueService
{
    /** @var \Redis */
    private $redis;

    /** @var StatusService */
    private $statusService;

    public function __construct(\Redis $redis, StatusService $statusService)
    {
        $this->redis = $redis;
        $this->statusService = $statusService;
    }

    public function enqueue(string $queue, JobInterface $job): TrackedJobInterface
    {
        $id = uniqid('', true) . uniqid('', true);

        $trackedJob = new TrackedJob($id, $job);

        $this->statusService->createStatus($trackedJob);

        $this->redis->lPush($queue, $job->getBody());

        return $trackedJob;
    }
}