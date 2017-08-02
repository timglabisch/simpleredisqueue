<?php

namespace Tg\RedisQueue\Redis\Key;

class Key
{
    /** @var string */
    private $key;

    private $token;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function __toString()
    {
        return $this->key;
    }

    public function getToken()
    {
        if ($this->token !== null) {
            return $this->token;
        }

        return $this->token = base64_encode(random_bytes(32));
    }
}