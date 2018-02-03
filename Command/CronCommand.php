<?php

namespace Bordeux\Bundle\CronBundle\Command;

use Bordeux\Bundle\CronBundle\Entity\Cron;
use Bordeux\Bundle\CronBundle\Repository\CronLogRepository;
use Bordeux\Bundle\CronBundle\Repository\CronRepository;
use Doctrine\DBAL\LockMode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * Configuration method
     */
    protected function configure()
    {
        $this
            ->setName('bordeux:cron:run')
            ->setDescription('Run Crons')
            ->addOption(
                'daemon',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Never stop? Ok',
                0
            )->addOption(
                'sleep',
                's',
                InputOption::VALUE_OPTIONAL,
                'Sleep time in seconds',
                10
            );

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
     * @return CronLogRepository
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function getCronLogRepository()
    {
        return $this->getEm()
            ->getRepository(Cron\Log::class);
    }


    /**
     * @return Cron|null
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function getNextTask()
    {
        $task = $this->getCronRepository()
            ->getNextTask();


        if (!$task) {
            return null;
        }


        if ($this->lockTask($task)) {
            return $task;
        }

        return $this->getNextTask();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $daemon = (bool)$input->getOption('daemon');
        $sleep = (int)$input->getOption('sleep');

        $consolePath = static::getConsoleFilePath(
            $this->getContainer()
                ->getParameter("kernel.root_dir")
        );

        $environment = $this->getContainer()
            ->getParameter("kernel.environment");

        $phpBin = (new PhpExecutableFinder)->find();

        $this->getEm()->clear();

        $task = $this->getNextTask();

        if (!$task) {
            $output->writeln("No tasks to execute...");

            if ($daemon) {
                $output->writeln("Sleeping {$sleep}s...");
                sleep($sleep);
                return $this->execute($input, $output);
            }

            return;
        }


        /** @var CronLogRepository $logRepository */
        $logRepository = $this->getEm()
            ->getRepository(Cron\Log::class);


        $log = new Cron\Log();
        $log->setCron($task);
        $log->setStartDate(new \DateTime());
        $this->getEm()->persist($log);
        $this->getEm()->flush([$log]);


        $output->writeln("Start running {$task->getName()} task");
        $start = microtime(true);

        $process = new Process(
            "{$phpBin} {$consolePath} {$task->getCommand()} {$task->getArguments()} --env={$environment}"
        );
        $process->start();


        $outputBuffer = '';


        $lastUpdateTime = time();
        while ($process->isRunning()) {
            if ($task->getPid() !== $process->getPid()) {
                $task->setPid($process->getPid());
                $this->getEm()->flush($task);
            }


            $outputs = [
                $process->getIncrementalOutput(),
                $process->getIncrementalErrorOutput()
            ];

            foreach ($outputs as $key => $item) {
                if (!empty(trim($item))) {
                    $outputBuffer .= $item;
                }
            }


            if (!empty($outputBuffer) && time() - $lastUpdateTime > 5) {
                try {
                    $logRepository->appendLog(
                        $log,
                        $outputBuffer
                    );
                    $outputBuffer = '';
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }
            sleep(2);
        }

        $end = microtime(true);
        $duration = $end - $start;

        $this->getEm()->refresh($log);
        $this->getEm()->refresh($task);

        $task->setPid(null);
        $task->setRunning(false);
        $task->setNextRunDate(new \DateTime($task->getInterval()));
        $task->setLastDuration($duration);

        $task->setErrors(
            $this->getCronLogRepository()
                ->getErrorCount(
                    $task
                )
        );


        $log->setEndDate(new \DateTime());
        $log->setSuccess($process->isSuccessful());
        $log->setOutput($process->getOutput() . $process->getErrorOutput());
        $log->setDuration($duration);
        $log->setResponseCode($process->getExitCode());

        $this->getEm()->flush();

        $output->writeln("End running {$task->getName()} task. ");

    }


    /**
     * @param Cron $cron
     * @return bool
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function lockTask(Cron $cron)
    {
        $classMetaData = $this->getEm()
            ->getClassMetadata(Cron::class);

        $table = $classMetaData->getTableName();
        $lockColumn = $classMetaData->getColumnName('running');
        $lastRunDateColumn = $classMetaData->getColumnName('lastRunDate');

        $connection = $this->getEm()
            ->getConnection();

        $query = "
            UPDATE 
                {$table}
            SET
              {$lockColumn} =  :lockValue,
              {$lastRunDateColumn} = :newUpdateDate
            WHERE
              id = :id
            AND
              {$lockColumn} =  :lockColumn
            AND
              {$lastRunDateColumn} = :lastRunDateColumn
        ";


        try {
            $connection->beginTransaction();
            $statement = $connection->prepare($query);
            $statement->bindValue(':lockValue', true, \PDO::PARAM_BOOL);
            $statement->bindValue(':lockColumn', false, \PDO::PARAM_BOOL);
            $statement->bindValue(':id', $cron->getId(), \PDO::PARAM_INT);
            $statement->bindValue(
                ':lastRunDateColumn'
                , $cron->getLastRunDate(),
                $cron->getLastRunDate() ? "datetime" : \PDO::PARAM_NULL
            );
            $statement->bindValue(':newUpdateDate', new \DateTime(), "datetime");
            $statement->execute();
            $count = $statement->rowCount();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $count = 0;
        }

        $this->getEm()->refresh($cron);
        return !!$count;
    }


}
