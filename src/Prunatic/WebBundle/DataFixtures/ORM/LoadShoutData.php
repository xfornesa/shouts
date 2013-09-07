<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\Vote;
use Prunatic\WebBundle\Entity\Point;

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
            // basic fields
            $point = new Point(41.536691 * (1+rand(1, 9)/1000 - rand(1, 9)/1000),2.443804 * (1+rand(1, 9)/1000 - rand(1, 9)/1000));
            $shout
                ->setEmail(sprintf('email.%s@elmeucrit.cat', $i))
                ->setAuthor(sprintf('Author %s', $i))
                ->setMessage($messages[rand(0, count($messages)-1)])
                ->setPoint($point)
                ->approve()
            ;
            // votes
            $numVotes =  rand(0, 10);
            for ($j=0; $j<$numVotes; $j++) {
                $ip = $this->getFakeIp($j+1);
                $shout->addVote(new Vote($ip));
            }
            // reports
            $numReports =  rand(0, 3 * rand(0, 1));
            for ($j=0; $j<$numReports; $j++) {
                $ip = $this->getFakeIp($j+1);
                $shout->reportInappropriate($ip);
            }
            // persist the shout
            $manager->persist($shout);
        }
        $manager->flush();
    }

    /**
     * Generate a fake IP based on param $i
     *
     * @param int $i
     * @return string
     */
    private function getFakeIp($i = 1)
    {
        return sprintf('127.0.0.%s', $i);
    }
}