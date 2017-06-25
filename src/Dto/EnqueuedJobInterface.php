<?php

namespace Tg\RedisQueue\Dto;


interface EnqueuedJobInterface
{
    public function encode(): string;

    public function getJobId();
}