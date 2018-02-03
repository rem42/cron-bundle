<?php

namespace Bordeux\Bundle\CronBundle\Entity;

use Bordeux\Bundle\CronBundle\Entity\Cron\Log;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cron
 *
 * @ORM\Table(name="cron__task")
 * @ORM\Entity(repositoryClass="Bordeux\Bundle\CronBundle\Repository\CronRepository")
 */
class Cron
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    protected $createDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="running", type="boolean", options={"default" : 0})
     */
    protected $running;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="string", length=255)
     */
    protected $command;

    /**
     * @var string
     *
     * @ORM\Column(name="arguments", type="text", nullable=true)
     */
    protected $arguments;

    /**
     * @var string
     *
     * @ORM\Column(name="interval_time", type="string", length=255)
     */
    protected $interval;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nextRunDate", type="datetime")
     */
    protected $nextRunDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastRunDate", type="datetime", nullable=true)
     */
    protected $lastRunDate;

    /**
     * @var string
     *
     * @ORM\Column(name="pid", type="integer", nullable=true)
     */
    protected $pid;

    /**
     * Duration in seconds
     *
     * @var float
     *
     * @ORM\Column(name="last_duration", type="float", nullable=true)
     */
    protected $lastDuration;


    /**
     * @var string
     *
     * @ORM\Column(name="errors", type="integer", options={"default" : 0})
     */
    protected $errors;


    /**
     * @var Log[]
     *
     * @ORM\OneToMany(targetEntity="Bordeux\Bundle\CronBundle\Entity\Cron\Log", mappedBy="cron", indexBy="id")
     */
    protected $logs;


    /**
     * Cron constructor.
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function __construct()
    {
        $this->createDate = new \DateTime();
        $this->nextRunDate = new \DateTime();
        $this->lastRunDate = new \DateTime();
        $this->lastRunDate->setDate(2018, 01, 01);
        
        $this->logs = new ArrayCollection();
        $this->interval = "+1 day";
        $this->enabled = true;
        $this->running = false;
        $this->pid = null;
        $this->errors = 0;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Cron
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param bool $enabled
     * @return Cron
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Cron
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Cron
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set command
     *
     * @param string $command
     *
     * @return Cron
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set arguments
     *
     * @param string $arguments
     *
     * @return Cron
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get arguments
     *
     * @return string
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set interval
     *
     * @param string $interval
     *
     * @return Cron
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval
     *
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set nextRunDate
     *
     * @param \DateTime $nextRunDate
     *
     * @return Cron
     */
    public function setNextRunDate($nextRunDate)
    {
        $this->nextRunDate = $nextRunDate;

        return $this;
    }

    /**
     * Get nextRunDate
     *
     * @return \DateTime
     */
    public function getNextRunDate()
    {
        return $this->nextRunDate;
    }

    /**
     * Set lastRunDate
     *
     * @param \DateTime $lastRunDate
     *
     * @return Cron
     */
    public function setLastRunDate($lastRunDate)
    {
        $this->lastRunDate = $lastRunDate;

        return $this;
    }

    /**
     * Get lastRunDate
     *
     * @return \DateTime
     */
    public function getLastRunDate()
    {
        return $this->lastRunDate;
    }


    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return bool
     */
    public function isRunning()
    {
        return $this->running;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param bool $running
     * @return Cron
     */
    public function setRunning($running)
    {
        $this->running = $running;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param string $pid
     * @return Cron
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return Log[]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return float
     */
    public function getLastDuration()
    {
        return $this->lastDuration;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param float $lastDuration
     * @return Cron
     */
    public function setLastDuration($lastDuration)
    {
        $this->lastDuration = $lastDuration;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param string $errors
     * @return Cron
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }


    /**
     * @return int
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function getPercentage()
    {
        if ($this->getLastDuration() <= 0 || !$this->getLastRunDate()) {
            return 0;
        }

        $seconds = time() - $this->getLastRunDate()->getTimestamp();
        $durationTime = (int) $this->getLastDuration();

        return min((($seconds+1) / $durationTime) * 100, 100);
    }


    /**
     * @return string
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function __toString()
    {
        return (string) $this->getName();
    }


}

