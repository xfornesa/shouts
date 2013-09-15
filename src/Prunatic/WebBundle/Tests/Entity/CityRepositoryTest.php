<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Tests\Entity\BaseRepositoryTest;
use Prunatic\WebBundle\Entity\City;
use Prunatic\WebBundle\Entity\Province;
use Prunatic\WebBundle\Entity\Country;

class CityRepositoryTest extends BaseRepositoryTest
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

        $city = new City();
        $city
            ->setName('city')
            ->setProvince($province)
        ;

        $this->em->persist($country);
        $this->em->persist($province);
        $this->em->persist($city);
        $this->em->flush();
        $this->em->clear();

        $result = $this->em
            ->getRepository('PrunaticWebBundle:City')
            ->findBySlugWithProvinceAndCountry($city->getSlug(), $province->getSlug(), $country->getSlug())
        ;
        $this->assertEquals($city->getId(), $result->getId());
    }

}
