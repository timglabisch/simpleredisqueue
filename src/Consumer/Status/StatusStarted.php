<?php

namespace Tg\RedisQueue\Consumer\Status;

class StatusStarted implements StatusInterface
{
    private $worker;

    private $queue;

    private $timestamp;

    private $workQueue;

    public function __construct($worker, $queue, $timestamp, $workQueue)
    {
        $this->worker = $worker;
        $this->queue = $queue;
        $this->timestamp = $timestamp;
        $this->workQueue = $workQueue;
    }

    public function getWorker(): string
    {
        return $this->worker;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getWorkQueue()
    {
        return $this->workQueue;
    }

}