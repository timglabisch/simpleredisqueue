<?php

use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerContext;
use Tg\RedisQueue\Dto\Job;
use Tg\RedisQueue\Dto\JobInterface;
use Tg\RedisQueue\Service\Logger;
use Tg\RedisQueue\Redis\RedisFactory;
use Tg\RedisQueue\Service\ServiceContainer;

require __DIR__ . '/vendor/autoload.php';

$redis = RedisFactory::create('127.0.0.1', 6379, 8);

$container = new ServiceContainer(
    new Logger(),
    $redis
);

foreach (range(1, 1000) as $i) {

    $trackedJob = $container->getJobEnqueueService()->enqueue('queue1', new Job('message ' . time()));

    /*
    $container->getStatusService()->addStatus($trackedJob, 'PERCENT', '1');
    $container->getStatusService()->addStatus($trackedJob, 'PERCENT', '10');
    $container->getStatusService()->addStatus($trackedJob, 'PERCENT', '50');

    $status = $container->getStatusService()->getStatus($trackedJob);

    $percent = $status->getLatestEntryWithIdentifier('PERCENT');
    $finish = $status->getLatestEntryWithIdentifier('FINISH');
    */
}