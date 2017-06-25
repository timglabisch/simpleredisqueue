<?php

namespace Tg\RedisQueue\Consumer;

class ConsumerContext
{
    private $redis = null;
    private $redisPort = null;
    private $consumerNum;
    private $queue;
    private $maxJobsToDequeue = 1;
    private $tickTimeout = 1;
    private $timeout = 1;

    public static function newFromEnv(): ConsumerContext
    {
        return new ConsumerContext(
            getenv('REDIS') ?: die('Environment Variable REDIS must be given'),
            getenv('REDIS_PORT') ?: 6379,
            getenv('CONSUMER') ?: die('Environment Variable CONSUMER must be given'),
            getenv('QUEUE') ?: die('Environment Variable QUEUE must be given'),
            getenv('MAX_JOBS_TO_DEQUEUE') ?: 1,
            getenv('TICK_TIMEOUT') ?: 1,
            getenv('TIMEOUT') ?: 10
        );
    }

    public function __construct($redis, $redisPort, $consumerNum, $queue, $maxJobsToDequeue = 1, $tickTimeout = 1, $timeout = 2)
    {
        $this->redis = $redis;
        $this->redisPort = $redisPort;
        $this->consumerNum = $consumerNum;
        $this->queue = $queue;
        $this->maxJobsToDequeue = $maxJobsToDequeue;
        $this->tickTimeout = $tickTimeout;
        $this->timeout = $timeout;
    }

    /**
     * @return null
     */
    public function getRedis()
    {
        return $this->redis;
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
        return 'work_' . $this->getQueue() . '_' . $this->getConsumerNum();
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

    /**
     * @return null
     */
    public function getRedisPort()
    {
        return $this->redisPort;
    }

}