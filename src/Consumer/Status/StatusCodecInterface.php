<?php

namespace Tg\RedisQueue\Consumer\Status;


interface StatusCodecInterface
{
    public function supportsDecode(string $data): bool;

    public function supportsEncode(StatusInterface $status): bool;

    public function decode(string $data): StatusInterface;

    public function encode(StatusInterface $status): string;

}