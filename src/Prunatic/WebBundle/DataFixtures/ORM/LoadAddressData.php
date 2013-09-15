<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Prunatic\WebBundle\Entity\Country;
use Prunatic\WebBundle\Entity\Province;
use Prunatic\WebBundle\Entity\City;

class LoadAddressData implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    function getOrder()
    {
        return 5;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $countries = array(
            array(
                'name' => 'Catalunya',
                'provinces' => array(
                    array(
                        'name' => 'Barcelona',
                        'cities' => array(
                            'Mataró',
                            'Arenys de Munt',
                            'Canet de Mar'
                        )
                    )
                )
            )
        );

        foreach ($countries as $country) {
            $oCountry = new Country();
            $oCountry->setName($country['name']);
            $manager->persist($oCountry);

            // provinces
            foreach ($country['provinces'] as $province) {
                $oProvince = new Province();
                $oProvince->setName($province['name']);
                $oProvince->setCountry($oCountry);
                $manager->persist($oProvince);

                // cities
                foreach ($province['cities'] as $city) {
                    $oCity = new City();
                    $oCity->setName($city);
                    $oCity->setProvince($oProvince);
                    $manager->persist($oCity);
                }
            }
        }
        $manager->flush();
    }
}