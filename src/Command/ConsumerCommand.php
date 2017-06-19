<?php

namespace Tg\RedisQueue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\RedisQueue\ConsumerContext;
use Tg\RedisQueue\Service\ConsumeService;

class ConsumerCommand extends Command
{
    /** @var ConsumeService */
    private $consumeService;

    public function __construct(ConsumeService $consumeService)
    {
        parent::__construct('consume');
        $this->consumeService = $consumeService;
    }

    protected function configure()
    {
        $this->setName('consumer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerContext = new ConsumerContext(
            '1',
            'queue1',
            5
        );

        $uncommitedJobs = $this->consumeService->getJobsFromWorkingQueue($consumerContext);

        if ($uncommitedJobs) {
            $output->writeln("Found Uncommited Jobs, start to recover.");

            $this->processJobs($uncommitedJobs, $consumerContext, $output);
        }


        while (true) {

            $output->writeln("Start Waiting for Jobs");

            $jobs = $this->consumeService->getJobs($consumerContext);

            $this->processJobs($jobs, $consumerContext, $output);
        }

        $output->writeln("Finish");
    }

    private function processJobs(array $jobs, ConsumerContext $consumerContext, OutputInterface $output)
    {
        $output->writeln(sprintf("collected %d jobs", count($jobs)));

        if (!count($jobs)) {
            $output->writeln(sprintf("no jobs."));
        }

        $output->writeln("Start working on jobs");

        foreach ($jobs as $job) {
            $output->writeln("Work on Job " . $job);
        }

        if (!empty($jobs)) {
            $output->writeln("Commit Work");
            $this->consumeService->commitWorkQueue($consumerContext);
        }

        $output->writeln("Finish Work");
    }

}