<?php

namespace Prunatic\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Prunatic\WebBundle\Entity\Shout;

class LoadShoutData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $shout = new Shout();
        $shout->setEmail('xfornesa@gmail.com');
        $shout->setAuthor('Xavier');
        $shout->setMessage('INDEPENDÈNCIA!!!');

        $manager->persist($shout);
        $manager->flush();
    }
}