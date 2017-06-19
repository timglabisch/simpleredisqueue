<?php

namespace Tg\RedisQueue;

class ConsumerContext
{
    private $consumerNum;
    private $queue;
    private $maxJobsToDequeue = 1;
    private $tickTimeout = 0.1;
    private $timeout = 1;


    public function __construct($consumerNum, $queue, $maxJobsToDequeue = 1, $tickTimeout = 1, $timeout = 2)
    {
        $this->consumerNum = $consumerNum;
        $this->queue = $queue;
        $this->maxJobsToDequeue = $maxJobsToDequeue;
        $this->tickTimeout = $tickTimeout;
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getConsumerNum()
    {
        return $this->consumerNum;
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->queue;
    }

    public function getWorkQueue()
    {
        return 'work_'.$this->getQueue().'_'.$this->getConsumerNum();
    }

    /**
     * @return int
     */
    public function getMaxJobsToDequeue()
    {
        return $this->maxJobsToDequeue;
    }

    /**
     * @return float
     */
    public function getTickTimeout()
    {
        return $this->tickTimeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

}