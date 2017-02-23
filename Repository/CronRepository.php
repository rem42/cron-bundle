<?php
namespace Bordeux\Bundle\CronBundle\Repository;

use Bordeux\Bundle\CronBundle\Entity\Cron;

/**
 * CronRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CronRepository extends \Doctrine\ORM\EntityRepository
{


    /**
     * @return Cron|null
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function getNextTask(){
        $prev = new \DateTime();
        $prev->setDate(1993, 6, 11);

        return $this->createQueryBuilder("t")
            ->addSelect("COALESCE(t.lastRunDate, :prev) as HIDDEN _sort")
            ->andWhere("t.running = false")
            ->andWhere("t.nextRunDate IS NULL OR t.nextRunDate <= :now")
            ->andWhere("t.enabled = true")
            ->setParameter(":prev", $prev)
            ->setParameter(
                ":now",
                new \DateTime()
            )->setMaxResults(1)
            ->orderBy("_sort", "ASC")
            ->getQuery()
            ->getOneOrNullResult();
    }
}
