<?php

namespace Tg\RedisQueue;


interface TrackedJobInterface extends JobInterface
{
    public function getJobId();
}