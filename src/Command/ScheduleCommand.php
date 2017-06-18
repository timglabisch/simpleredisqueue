<?php

namespace Tg\RedisQueue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\RedisQueue\Consumer\Service\ScheduleService;

class ScheduleCommand extends Command
{
    /** @var ScheduleService */
    private $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        parent::__construct('schedule');
        $this->scheduleService = $scheduleService;
    }


    protected function configure()
    {
        $this->setName('schedule');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        foreach (range(0, 1000) as $v) {
            $this->scheduleService->enqueue(
                new Schedule(
                    new \DateTime(),
                    'some Job'
                )
            );
        }
        */


        $this->scheduleService->schedule((new \DateTime())->modify('-10 seconds'), 'wrk');

        $a = 0;
    }

}