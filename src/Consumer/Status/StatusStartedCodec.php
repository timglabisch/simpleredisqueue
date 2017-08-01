<?php

namespace Tg\RedisQueue\Consumer\Status;


class StatusStartedCodec implements StatusCodecInterface
{
    public function supportsDecode(string $data): bool
    {
        return isset($data[0], $data[1]) && $data[0] === 's' && $data[1] === '|';
    }

    public function supportsEncode(StatusInterface $status): bool
    {
        return $status instanceof StatusStarted;
    }

    public function decode(string $data): StatusInterface
    {
        // format s|[worker]|timestamp|queue|work_queue

        $data = explode('|', $data, 5);

        if (count($data) !== 5) {
            throw new CodecException("expected 3 data elements");
        }

        return new StatusStarted($data[1], $data[3], $data[2], $data[4]);
    }

    /**
     * @param StatusInterface|StatusStarted $status
     * @return string
     */
    public function encode(StatusInterface $status): string
    {
        return 's|'.$status->getWorker().'|'.$status->getTimestamp().'|'.$status->getQueue().'|'.$status->getWorkQueue();
    }
}