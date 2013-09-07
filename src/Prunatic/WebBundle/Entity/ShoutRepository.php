<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Prunatic\WebBundle\Entity\Shout;

/**
 * ShoutRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShoutRepository extends EntityRepository
{
    /**
     * Prepare base query builder object for visible Shouts
     *
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function qbVisibleShouts($offset = null, $limit = null)
    {
        $visibleStatus = array(Shout::STATUS_APPROVED);
        $qb = $this->createQueryBuilder('s');
        if (!is_null($offset)) {
            $qb->setFirstResult($offset);
        }
        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->where(
                $qb->expr()->in('s.status', $visibleStatus)
            )
        ;
    }

    /**
     * Prepare a query builder for visible shouts ordered by created
     *
     * @see http://gist.github.com/arnaud-lb/2704404 to force use index
     * @see https://doctrine-orm.readthedocs.org/en/latest/cookbook/dql-custom-walkers.html?highlight=setHint#generic-count-query-for-pagination
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function qbNewestVisibleShouts($offset = null, $limit = null)
    {
        $qb = $this->qbVisibleShouts($offset, $limit);

        // TODO optimize query ordered by date and filtered by status, actually it does not use right indexes

        return $qb
            ->orderBy('s.created', 'desc')
            ->addOrderBy('s.id', 'desc')
        ;
    }

    /**
     * Get shouts approved ordered by created
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getNewestVisibleShouts($offset = 0, $limit = 10)
    {
        return $this->qbNewestVisibleShouts($offset, $limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Prepare a query builder for visible shouts ordered by total votes (top rated)
     *
     * @see http://gist.github.com/arnaud-lb/2704404 to force use index
     * @see https://doctrine-orm.readthedocs.org/en/latest/cookbook/dql-custom-walkers.html?highlight=setHint#generic-count-query-for-pagination
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function qbTopRatedVisibleShouts($offset = null, $limit = null)
    {
        $qb = $this->qbVisibleShouts($offset, $limit);

        // TODO optimize query ordered by total votes and filtered by status, actually it does not use right indexes
        return $qb
            ->orderBy('s.totalVotes', 'desc')
            ->addOrderBy('s.id', 'desc')
        ;
    }

    /**
     * Get visible shouts ordered by total votes
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getTopRatedVisibleShouts($offset = 0, $limit = 10)
    {
        return $this->qbTopRatedVisibleShouts($offset, $limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Prepare a query builder for visible shouts ordered by distance to the coordinates
     *
     * @param $latitude
     * @param $longitude
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function qbNearbyVisibleShouts($latitude, $longitude, $offset = null, $limit = null)
    {
        $qb = $this->qbVisibleShouts($offset, $limit);

        // TODO incomplete: implement a search by point and near results
        return $qb;
    }

    /**
     * Get nearby visible shouts from a given coordinates
     *
     * @param $latitude
     * @param $longitude
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getNearbyVisibleShouts($latitude, $longitude, $offset = null, $limit = null)
    {
        return $this->qbNearbyVisibleShouts($latitude, $longitude, $offset = null, $limit = null)
            ->getQuery()
            ->getResult()
            ;

    }
}
