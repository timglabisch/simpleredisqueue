<?php

namespace Tg\RedisQueue\Consumer\Status;


interface StatusInterface
{
    public function getWorker(): string;
}