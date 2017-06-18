<?php

namespace Tg\RedisQueue\Consumer\Service;


use Tg\RedisQueue\Consumer\Schedule;

class ScheduleService
{
    /** @var \Redis */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function enqueue(Schedule $schedule): bool
    {
        $timestamp = $schedule->getDate()->getTimestamp();

        $this->redis->multi();
        $this->redis->zAdd('scheduled', $timestamp, $timestamp);
        $this->redis->rpush('scheduled:' . $timestamp, $schedule->getJob());
        $res = $this->redis->exec();

        if (!is_array($res)) {
            return false;
        }

        if (count($res) !== 2) {
            return false;
        }

        if ($res[0] < 0) {
            return false;
        }

        if ($res[1] < 0) {
            return false;
        }

        return true;
    }

    /**
     * @param \DateTime $now
     * @return string|null
     */
    private function getNextTimestamp(\DateTime $now)
    {
        $items = $this->redis->zrangebyscore('scheduled', '-inf', $now->getTimestamp(), ['limit' => [0, 1]]);

        if (!$items || !is_array($items) || !isset($items[0])) {
            return null;
        }

        return $items[0];
    }

    public function schedule(\DateTime $now, string $workQueue)
    {
        while ($timestamp = $this->getNextTimestamp($now)) {
            while ($this->redis->lLen('scheduled:' . $timestamp)) {
                $x = $this->redis->rpoplpush('scheduled:' . $timestamp, $workQueue);

                if (!$x) {
                    continue;
                }

                echo "moved entry from " . 'scheduled:' . $timestamp . "\n";
            }

            $this->tryRemoveTimestamp($timestamp);
        }

    }

    private function tryRemoveTimestamp($timestamp)
    {
        $this->redis->zRem('scheduled', $timestamp);
        $this->redis->del('scheduled:' . $timestamp);
    }
}