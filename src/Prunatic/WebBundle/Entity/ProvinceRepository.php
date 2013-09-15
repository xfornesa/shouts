<?php

namespace Prunatic\WebBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProvinceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProvinceRepository extends EntityRepository
{
    private function qbFindBySlugWithCountry()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('p')
            ->innerJoin('p.country', 'co')
            ->where($qb->expr()->eq('p.slug', ':province'))
            ->andWhere($qb->expr()->eq('co.slug', ':country'))
        ;

        return $qb;
    }

    public function findBySlugWithCountry($province, $country)
    {
        $qb = $this->qbFindBySlugWithCountry();

        return $qb
            ->setParameters(array(
                'province' => $province,
                'country' => $country,
            ))
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
