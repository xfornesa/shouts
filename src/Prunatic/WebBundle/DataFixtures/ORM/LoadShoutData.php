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
        $messages = array(
            'Independència!!!',
            'Visca Catalunya independent!!!',
            'Catalunya és més que una nació!',
            'Catalunya is not Spain',
            'Hi han dos tipus de persones, els catalans i els que ho voldrien ser'
        );
        for ($i=0; $i<15; $i++) {
            $shout = new Shout();
            $shout->setEmail(sprintf('email.%s@elmeucrit.cat', $i));
            $shout->setAuthor(sprintf('Author %s', $i));
            $shout->setMessage($messages[rand(0, count($messages)-1)]);
            $shout->setLongitude(41.536691 * rand(0.001, 0.009));
            $shout->setLatitude(2.443804 * rand(0.001, 0.009));
            $numVotes =  rand(0, 10);
            for ($j=0; $j<$numVotes; $j++) {
                $shout->addVote(new Vote());
            }
            $manager->persist($shout);
        }
        $manager->flush();
    }
}