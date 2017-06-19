<?php

namespace Tg\RedisQueue;


class Schedule
{
    /** @var \DateTime */
    private $date;

    /** @var string */
    private $job;

    public function __construct(\DateTime $date, $job)
    {
        $this->date = $date;
        $this->job = $job;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getJob(): string
    {
        return $this->job;
    }

}