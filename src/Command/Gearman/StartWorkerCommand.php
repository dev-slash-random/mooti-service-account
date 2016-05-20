<?php
namespace Mooti\Service\Account\Command\Gearman;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mooti\Framework\Framework;
use GearmanWorker;
use GearmanJob;
use Mooti\Service\Account\Model\User\UserMapper;

class StartWorkerCommand extends Command
{
    use Framework;

    protected function configure()
    {
        $this->setName('worker:start');
        $this->setDescription('Start a worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = new GearmanWorker();
        $worker->addServer();
        $worker->addFunction('mooti.account.user.cache', function (GearmanJob $job) {
            $userMapper = $this->createNew(UserMapper::class);
            $cacheItem = $userMapper->cacheUser($job->workload());
            print_r($cacheItem);
        });
        while ($worker->work());
        $output->writeln('done');
    }
}
