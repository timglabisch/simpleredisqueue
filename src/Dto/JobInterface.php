<?php

namespace Tg\RedisQueue\Dto;


interface JobInterface
{
    public function getBody(): string;
}