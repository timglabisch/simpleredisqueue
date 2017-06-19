<?php

namespace Tg\RedisQueue\Service;


class DateTimeProvider
{
    public function now(): \DateTime {
        return new \DateTime();
    }
}