<?php

namespace Prunatic\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\Vote;

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
        $shout->addVote(new Vote());
        $manager->persist($shout);

        $shout = new Shout();
        $shout->setEmail('margagrimal@hotmail.com');
        $shout->setAuthor('Marga');
        $shout->setMessage('VISCA CATALUNYA INDEPENDENT!!!');
        $shout->addVote(new Vote());
        $shout->addVote(new Vote());
        $shout->addVote(new Vote());
        $manager->persist($shout);

        $manager->flush();
    }
}