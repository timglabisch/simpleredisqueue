<?php

namespace Tg\RedisQueue\Service;


use Tg\RedisQueue\Dto\JobInterface;
use Tg\RedisQueue\Dto\EnqueuedJob;
use Tg\RedisQueue\Dto\EnqueuedJobInterface;
use Tg\RedisQueue\Status\StatusEntry;

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

    public function enqueue(string $queue, JobInterface $job, int $ttlTracking = 3600): EnqueuedJobInterface
    {
        $id = uniqid('', true) . uniqid('', true);

        $trackedJob = new EnqueuedJob($id, $job);

        $this->statusService->addStatus($trackedJob, StatusEntry::STATUS_CREATED, '', $ttlTracking);

        $this->redis->lPush($queue, $trackedJob->encode());

        return $trackedJob;
    }
}