<?php

namespace Tg\RedisQueue\Consumer\Service;


class DateTimeProvider
{
    public function now(): \DateTime {
        return new \DateTime();
    }
}