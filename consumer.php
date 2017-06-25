<?php

use Tg\RedisQueue\Consumer\ConsumerContext;
use Tg\RedisQueue\Consumer\IsolatedConsumerContext;
use Tg\RedisQueue\Dto\JobInterface;
use Tg\RedisQueue\Service\Logger;
use Tg\RedisQueue\Redis\RedisFactory;
use Tg\RedisQueue\Service\ServiceContainer;
use Tg\RedisQueue\Dto\EnqueuedJobInterface;

require __DIR__ . '/vendor/autoload.php';

$context = ConsumerContext::newFromEnv();

$container = new ServiceContainer(
    new Logger(),
    RedisFactory::create($context->getRedis(), $context->getRedisPort())
);

$container->getCommandConsumer()->execute(
    $container->getSimpleConsumerRuntime(),
    $context,
    new class implements \Tg\RedisQueue\Consumer\IsolatedConsumerInterface
    {

        /** @param JobInterface[] $jobs */
        public function handle(array $jobs, IsolatedConsumerContext $context)
        {
            foreach ($jobs as $job) {

                $result = $this->handleJob($job, $context);

                if ($result != static::RETURN_SUCCESS) {
                    return $result;
                }
            }

            return $this->finish();
        }

        private function handleJob(EnqueuedJobInterface $job, IsolatedConsumerContext $context)
        {
            $context->getLogger()->info('job:'. $job->getJobId());

            return static::RETURN_SUCCESS;
        }

        private function finish()
        {
            return static::RETURN_SUCCESS;
        }

    }
);