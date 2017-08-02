<?php

namespace Tg\RedisQueue\Service;


use Psr\Log\LoggerInterface;
use Tg\RedisQueue\Command\ConsumerCommand;
use Tg\RedisQueue\Command\ProducerCommand;
use Tg\RedisQueue\Command\ScheduleCommand;
use Tg\RedisQueue\Consumer\Runtime\ConsumerRuntimeInterface;
use Tg\RedisQueue\Consumer\Runtime\SimpleConsumerRuntime;
use Tg\RedisQueue\Consumer\Service\ConsumerStatusService;
use Tg\RedisQueue\Redis\LockHandler;
use Tg\RedisQueue\Service\ConsumeService;
use Tg\RedisQueue\Service\DateTimeProvider;
use Tg\RedisQueue\Service\JobEnqueueService;
use Tg\RedisQueue\Service\ScheduleService;
use Tg\RedisQueue\Service\StatusService;

class ServiceContainer
{
    /** @var ConsumerCommand */
    private $commandConsumer;

    /** @var ProducerCommand */
    private $commandProducer;

    /** @var ScheduleCommand */
    private $commandScheduler;

    /** @var ConsumeService */
    private $consumeService;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var JobEnqueueService */
    private $jobEnqueueService;

    /** @var ScheduleService */
    private $scheduleService;

    /** @var StatusService */
    private $statusService;

    /** @var ConsumerRuntimeInterface */
    private $simpleConsumerRuntime;

    /** @var ConsumerStatusService */
    private $consumerStatusService;

    /** @var LockHandler */
    private $lockHandler;

    /** @var LoggerInterface */
    private $logger;

    /** @var \Redis */
    private $redis;

    public function __construct(LoggerInterface $logger, \Redis $redis)
    {
        $this->logger = $logger;
        $this->redis = $redis;
    }

    public function getLockHandler(): LockHandler
    {
        return $this->lockHandler ?? new LockHandler($this->getRedis());
    }

    public function getCommandConsumer(): ConsumerCommand
    {
        if (!$this->commandConsumer) {
            $this->commandConsumer = new ConsumerCommand(
                $this->getConsumeService(),
                $this->getConsumerStatusService(),
                $this->getLogger()
            );
        }

        return $this->commandConsumer;
    }

    public function getConsumerStatusService(): ConsumerStatusService
    {
        if (!$this->consumerStatusService) {
            $this->consumerStatusService = new ConsumerStatusService(
                $this->redis
            );
        }

        return $this->consumerStatusService;
    }


    public function getCommandProducer(): ProducerCommand
    {
        if (!$this->commandProducer) {
            $this->commandProducer = new ProducerCommand(
                $this->getJobEnqueueService(),
                $this->getStatusService()
            );
        }
    }

    public function getCommandScheduler(): ScheduleCommand
    {
        if (!$this->commandScheduler) {
            $this->commandScheduler = new ScheduleCommand(
                $this->getScheduleService()
            );
        }

        return $this->commandScheduler;
    }

    public function getConsumeService(): ConsumeService
    {
        if (!$this->consumeService) {
            $this->consumeService = new ConsumeService(
                $this->redis
            );
        }

        return $this->consumeService;
    }

    public function getDateTimeProvider(): DateTimeProvider
    {
        if (!$this->dateTimeProvider) {
            $this->dateTimeProvider = new DateTimeProvider();
        }

        return $this->dateTimeProvider;
    }

    public function getJobEnqueueService(): JobEnqueueService
    {
        if (!$this->jobEnqueueService) {
            $this->jobEnqueueService = new JobEnqueueService(
                $this->redis,
                $this->getStatusService()
            );
        }

        return $this->jobEnqueueService;
    }

    public function getScheduleService(): ScheduleService
    {
        if (!$this->scheduleService) {
            $this->scheduleService = new ScheduleService(
                $this->redis
            );
        }

        return $this->scheduleService;
    }

    public function getStatusService(): StatusService
    {
        if (!$this->statusService) {
            $this->statusService = new StatusService(
                $this->redis,
                $this->getDateTimeProvider()
            );
        }

        return $this->statusService;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getSimpleConsumerRuntime(): ConsumerRuntimeInterface
    {
        if (!$this->simpleConsumerRuntime) {
            $this->simpleConsumerRuntime = new SimpleConsumerRuntime(
                $this->getJobEnqueueService(),
                $this->getStatusService(),
                $this->getLogger()
            );
        }

        return $this->simpleConsumerRuntime;
    }

    public function getRedis(): \Redis
    {
        return $this->redis;
    }

}