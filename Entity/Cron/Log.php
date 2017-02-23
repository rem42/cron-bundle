<?php

namespace Bordeux\Bundle\CronBundle\Entity\Cron;

use Bordeux\Bundle\CronBundle\Entity\Cron;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cron
 *
 * @ORM\Table(name="cron__task_log")
 * @ORM\Entity(repositoryClass="Bordeux\Bundle\CronBundle\Repository\CronLogRepository")
 */
class Log
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
     * @var Cron
     *
     * @ORM\ManyToOne(targetEntity="Bordeux\Bundle\CronBundle\Entity\Cron")
     * @ORM\JoinColumn(name="cron_task_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $cron;

    /**
     * Cron end date
     *
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * Cron start date
     *
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * Duration in seconds
     *
     * @var float
     *
     * @ORM\Column(name="duration", type="float", nullable=true)
     */
    protected $duration;


    /**
     * @var string
     *
     * @ORM\Column(name="output", type="text", nullable=true)
     */
    protected $output;


    /**
     * @var integer
     *
     * @ORM\Column(name="response_code", type="integer", nullable=true)
     */
    protected $responseCode;


    /**
     * @var boolean
     *
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    protected $success;


    /**
     * @var string
     *
     * @ORM\Column(name="ignore", type="boolean", options={"default" : 1})
     */
    protected $ignore;

    /**
     * Log constructor.
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function __construct()
    {
        $this->ignore = false;
    }


    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return Cron
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param Cron $cron
     * @return Log
     */
    public function setCron($cron)
    {
        $this->cron = $cron;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param \DateTime $startDate
     * @return Log
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param \DateTime $endDate
     * @return Log
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param float $duration
     * @return Log
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param string $output
     * @return Log
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param int $responseCode
     * @return Log
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @author Chris Bednarczyk <chris@tourradar.com>
     * @param bool $success
     * @return Log
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return string
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function __toString()
    {
        return $this->getId() ? "#{$this->getId()}" : '';
    }


}

