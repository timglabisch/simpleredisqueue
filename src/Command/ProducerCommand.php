<?php

namespace Tg\RedisQueue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\RedisQueue\Consumer\Service\JobEnqueueService;
use Tg\RedisQueue\Consumer\Service\StatusService;
use Tg\RedisQueue\Job;

class ProducerCommand extends Command
{
    /** @var JobEnqueueService */
    private $jobEnqueueService;

    /** @var StatusService */
    private $statusService;

    public function __construct(
        JobEnqueueService $jobEnqueueService,
        StatusService $statusService
    )
    {
        parent::__construct('producer');
        $this->jobEnqueueService = $jobEnqueueService;
        $this->statusService = $statusService;
    }

    protected function configure()
    {
        $this->setName('producer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $trackedJob = $this->jobEnqueueService->enqueue('queue1', new Job('message ' . time()));


        $this->statusService->addStatus($trackedJob, 'PERCENT', '1');
        $this->statusService->addStatus($trackedJob, 'PERCENT', '10');
        $this->statusService->addStatus($trackedJob, 'PERCENT', '50');

        $status = $this->statusService->getStatus($trackedJob);

        $percent = $status->getLatestEntryWithIdentifier('PERCENT');
        $finish = $status->getLatestEntryWithIdentifier('FINISH');

        $a = 0;

    }

}