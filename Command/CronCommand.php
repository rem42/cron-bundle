<?php

namespace Bordeux\Bundle\CronBundle\Command;

use Bordeux\Bundle\CronBundle\Entity\Cron;
use Bordeux\Bundle\CronBundle\Repository\CronRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class DetailsQueueCommand
 * @author Chris Bednarczyk <chris@tourradar.com>
 * @package TourRadar\Bundle\ApiBundle\Command\Queue
 */
class CronCommand extends ContainerAwareCommand
{

    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_per_page' => 32,
    ];



    /**
     * Configuration method
     */
    protected function configure()
    {
        $this
            ->setName('bordeux:cron:run')
            ->setDescription('Run Crons');

        parent::configure();
    }


    /**
     * @param string $kernelDir
     * @return string
     * @throws \Exception
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public static function getConsoleFilePath($kernelDir)
    {
        $paths = [
            "{$kernelDir}/../bin/console",
            "{$kernelDir}/../app/console",
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return realpath($path);
            }
        }
        throw new \Exception("Unable to find console file");
    }


    /**
     * @return \Doctrine\ORM\EntityManager|object
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function getEm()
    {
        return $this->getContainer()
            ->get("doctrine.orm.entity_manager");
    }


    /**
     * @return CronRepository
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function getCronRepository()
    {
        return $this->getEm()
            ->getRepository(Cron::class);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $consolePath = static::getConsoleFilePath(
            $this->getContainer()
                ->getParameter("kernel.root_dir")
        );

        $environment = $this->getContainer()
            ->getParameter("kernel.environment");


        $task = $this->getCronRepository()
            ->getNextTask();

        $phpBin = (new PhpExecutableFinder)->find();


        if (!$task) {
            $output->writeln("No tasks to execute...");
            return;
        }

        $task->setRunning(true);
        $task->setLastRunDate(new \DateTime());
        $this->getEm()->flush($task);

        $log = new Cron\Log();
        $log->setCron($task);
        $log->setStartDate(new \DateTime());
        $this->getEm()->persist($log);
        $this->getEm()->flush();


        $output->writeln("Start running {$task->getName()} task");
        $start = microtime(true);

        $process = new Process("{$phpBin} {$consolePath} {$task->getCommand()} {$task->getArguments()} --env={$environment}");
        $process->start();




        while ($process->isRunning()) {
            if ($task->getPid() !== $process->getPid()) {
                $task->setPid($process->getPid());
                $this->getEm()->flush($task);
            }
        }

        $end = microtime(true);
        $duration = $end - $start;

        $this->getEm()->refresh($task);

        $task->setPid(null);
        $task->setRunning(false);
        $task->setNextRunDate(new \DateTime($task->getInterval()));
        $task->setLastDuration($duration);

        !$process->isSuccessful() && $task->setErrors(
            ($task->getErrors() || 0) + 1
        );


        $log->setEndDate(new \DateTime());
        $log->setSuccess($process->isSuccessful());
        $log->setOutput($process->getOutput() . $process->getErrorOutput());
        $log->setDuration($duration);
        $log->setResponseCode($process->getExitCode());


        $this->getEm()->flush();

        $output->writeln("End running {$task->getName()} task. ");

    }


}
