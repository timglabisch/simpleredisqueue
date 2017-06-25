<?php

namespace Tg\RedisQueue\Dto;


use Tg\RedisQueue\Dto\EnqueuedJobInterface;
use Tg\RedisQueue\Dto\Job;
use Tg\RedisQueue\Dto\JobInterface;

class EnqueuedJob implements EnqueuedJobInterface
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

    public static function newFromDecode(string $str)
    {
        $data = explode('|', $str, 2);

        if (!isset($data[0], $data[1])) {
            throw new \InvalidArgumentException("input has no body / id");
        }

        return new self($data[0], new Job($data[1]));
    }

    public function encode(): string
    {
        return $this->getJobId() . '|' . $this->getJob()->getBody();
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