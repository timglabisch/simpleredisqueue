<?php

namespace Tg\RedisQueue;


interface JobInterface
{
    public function getBody(): string;
}