<?php

namespace Tg\RedisQueue\Dto;

use Tg\RedisQueue\Dto\JobInterface;

class Job implements JobInterface
{
    /** @var string */
    private $body;

    public function __construct(string $body)
    {
        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}