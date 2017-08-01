<?php

namespace Tg\RedisQueue\Dto;


interface EnqueuedJobInterface
{
    public function encode(): string;

    public function getJobId();

    public function getBody(): string;
}