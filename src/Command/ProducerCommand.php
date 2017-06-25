<?php

namespace Tg\RedisQueue\Command;

use Tg\RedisQueue\Service\JobEnqueueService;
use Tg\RedisQueue\Service\StatusService;
use Tg\RedisQueue\Dto\Job;

class ProducerCommand
{
    /** @var JobEnqueueService */
    private $jobEnqueueService;

    /** @var StatusService */
    private $statusService;

    public function __construct(
        JobEnqueueService $jobEnqueueService,
        StatusService $statusService
    ) {
        $this->jobEnqueueService = $jobEnqueueService;
        $this->statusService = $statusService;
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