<?php

namespace Tg\RedisQueue\Service;


use Tg\RedisQueue\Dto\JobInterface;
use Tg\RedisQueue\Dto\EnqueuedJob;
use Tg\RedisQueue\Dto\EnqueuedJobInterface;

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

    public function enqueue(string $queue, JobInterface $job): EnqueuedJobInterface
    {
        $id = uniqid('', true) . uniqid('', true);

        $trackedJob = new EnqueuedJob($id, $job);

        $this->statusService->createStatus($trackedJob);

        $this->redis->lPush($queue, $trackedJob->encode());

        return $trackedJob;
    }
}