<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Tests\Entity\BaseRepositoryTest;
use Prunatic\WebBundle\Entity\Province;
use Prunatic\WebBundle\Entity\Country;

class ProvinceRepositoryTest extends BaseRepositoryTest
{
    public function testFindCityBySlugWithProvinceAndCountry()
    {
        $country = new Country();
        $country->setName('Country');

        $province = new Province();
        $province
            ->setName('Province')
            ->setCountry($country)
        ;

        $this->em->persist($country);
        $this->em->persist($province);
        $this->em->flush();
        $this->em->clear();

        $result = $this->em
            ->getRepository('PrunaticWebBundle:Country')
            ->findBySlugWithCountry($province->getSlug(), $country->getSlug())
        ;
        $this->assertEquals($province->getId(), $result->getId());
    }
}
