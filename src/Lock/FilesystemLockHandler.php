<?php

namespace Tg\RedisQueue\Lock;

use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Exception\CouldNotAcquireLockException;

class FilesystemLockHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function doInLock($string, callable $cb)
    {
        $file = '/'.sys_get_temp_dir().'/' . hash('sha256', $string);

        if (!$handle = @fopen($file, 'r')) {
            if ($handle = @fopen($file, 'x')) {
                @chmod($file, 0444);
            } elseif (!$handle = @fopen($file, 'r')) {
                usleep(100); // Give some time for chmod() to complete
                $handle = @fopen($file, 'r');
            }
        }

        if (!$handle) {
            throw new CouldNotAcquireLockException();
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            throw new CouldNotAcquireLockException();
        }

        $ret = $cb();

        flock($handle, LOCK_UN | LOCK_NB);
        fclose($handle);

        return $ret;
    }

}