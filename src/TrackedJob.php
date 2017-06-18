<?php

namespace Tg\RedisQueue;


class TrackedJob implements TrackedJobInterface
{
    /** @var string */
    private $jobId;

    /** @var JobInterface */
    private $job;

    public function __construct(string $jobId, JobInterface $job)
    {
        $this->jobId = $jobId;
        $this->job = $job;
    }

    public function getBody(): string
    {
        return $this->job->getBody();
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }

}