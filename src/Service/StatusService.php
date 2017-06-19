<?php

namespace Tg\RedisQueue\Service;

use Tg\RedisQueue\JobInterface;
use Tg\RedisQueue\Status;
use Tg\RedisQueue\Status\StatusEntry;
use Tg\RedisQueue\TrackedJob;
use Tg\RedisQueue\TrackedJobInterface;

class StatusService
{
    /** @var \Redis */
    private $redis;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    public function __construct(\Redis $redis, DateTimeProvider $dateTimeProvider)
    {
        $this->redis = $redis;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public function createStatus(TrackedJobInterface $trackedJob)
    {
        $this->addStatus($trackedJob, StatusEntry::STATUS_CREATED, '');
    }

    public function addStatus(TrackedJobInterface $trackedJob, $identifer, string $value)
    {
        $statuskey = 'status:' . $trackedJob->getJobId();

        $this->redis->multi();
        $this->redis->rPush(
            $statuskey,
            (new StatusEntry($identifer, $value, $this->dateTimeProvider->now()))->__toString()
        );
        $this->redis->expire($statuskey, 10);//60 * 60 * 24);
        $res = $this->redis->exec();

        if (!is_array($res) || !isset($res[0], $res[1]) || !$res[0] || !$res[1]) {
            throw new \RuntimeException("Could not create Status");
        }
    }

    public function getStatus(TrackedJobInterface $trackedJob): Status
    {
        return $this->getStatusByJobId($trackedJob->getJobId());
    }

    public function getStatusByJobId(string $jobId): Status
    {
        $statuskey = 'status:' . $jobId;

        $items = $this->redis->lRange($statuskey, 0, -1);

        if (empty($items)) {
            return Status::newUnkownStatus();
        }

        $statusEntries = [];
        foreach ($items as $item) {
            $statusEntries[] = StatusEntry::fromString($item);
        }

        return new Status($statusEntries);
    }

}