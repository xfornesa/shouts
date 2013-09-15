<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Tests\Entity\BaseRepositoryTest;
use Prunatic\WebBundle\Entity\Point;
use Prunatic\WebBundle\Entity\Shout;

class ShoutRepositoryTest extends BaseRepositoryTest
{
    public function testEmptyShouts()
    {
        $shouts = $this->em
            ->getRepository('PrunaticWebBundle:Shout')
            ->findAll()
        ;

        $this->assertEmpty($shouts, 'Ensure that the shout table is empty');
    }

    public function testGetLastVisibleShouts()
    {
        /** @var Shout $shout */
        $insertedAuthors = array();

        // load examples
        for ($i=0; $i<10; $i++) {
            $author = sprintf('author-id-%010s', $i);

            $shout = new Shout();
            $shout
                ->setAuthor($author)
                ->setEmail('example@example.org')
                ->setMessage('')
            ;
            if ($i % 3 > 0) {
                $shout->approve();
            }
            if ($shout->isVisible()) {
                $insertedAuthors[] = $author;
            }

            $this->em->persist($shout);
        }
        $this->em->flush();
        $this->em->clear();

        // retrieve values
        $shouts = $this->em
            ->getRepository('PrunaticWebBundle:Shout')
            ->getNewestVisibleShouts()
        ;

        // assert there are values
        $this->assertNotEmpty($shouts);

        // assert order values retrieved
        $resultAuthors = array();

        foreach ($shouts as $shout) {
            $resultAuthors[] = $shout->getAuthor();
        }
        $insertedAuthors = array_reverse($insertedAuthors);
        $this->assertEquals($insertedAuthors, $resultAuthors);
    }

    public function testGetTopRatedVisibleShouts()
    {
        /** @var Shout $shout */
        // load examples
        for ($i=0; $i<10; $i++) {
            $votes = rand(0, 11);
            $author = sprintf('author-votes-%010s-id-%010s', $votes, $i);

            $shout = new Shout();
            $shout
                ->setAuthor($author)
                ->setEmail('example@example.org')
                ->setMessage('')
            ;
            $shout->setTotalVotes($votes);
            if ($i % 3 > 0) {
                $shout->approve();
            }
            if ($shout->isVisible()) {
                $insertedAuthors[] = $author;
            }

            $this->em->persist($shout);
        }
        $this->em->flush();
        $this->em->clear();

        // retrieve values
        $shouts = $this->em
            ->getRepository('PrunaticWebBundle:Shout')
            ->getTopRatedVisibleShouts()
        ;

        // assert there are values
        $this->assertNotEmpty($shouts);

        // assert order values retrieved
        $resultAuthors = array();

        foreach ($shouts as $shout) {
            $resultAuthors[] = $shout->getAuthor();
        }
        rsort($insertedAuthors);
        $this->assertEquals($insertedAuthors, $resultAuthors);
    }

    public function testGetNearbyVisibleShouts()
    {
        /** @var Shout $shout */
        // load examples
        $baseLongitude = 41.536691;
        $baseLatitude = 2.443804;
        $basePoint = new Point($baseLongitude, $baseLatitude);
        for ($i=0; $i<10; $i++) {
            $shout = new Shout();
            $longitude = $baseLongitude * (1 + rand(1, 9) / 1000 - rand(1, 9) / 1000);
            $latitude = $baseLatitude * (1 + rand(1, 9) / 1000 - rand(1, 9) / 1000);
            $point = new Point($longitude, $latitude);
            $shout
                ->setAuthor('Example author')
                ->setEmail('example@example.org')
                ->setMessage('')
                ->setPoint($point)
            ;
            if ($i % 3 > 0) {
                $shout->approve();
            }
            $this->em->persist($shout);
        }
        $this->em->flush();
        $this->em->clear();

        // retrieve values
        $shouts = $this->em
            ->getRepository('PrunaticWebBundle:Shout')
            ->getNearbyVisibleShouts($basePoint)
        ;

        // assert there are values
        $this->assertNotEmpty($shouts);

        // assert all values are visible
        foreach ($shouts as $shout) {
            $this->assertTrue($shout->isVisible(), 'Ensure that retrives only visible shouts');
        }

        // assert distance to the center is further for successive shouts
        $previousDistance = 0;
        foreach ($shouts as $shout) {
            $point = $shout->getPoint();
            $distance = $basePoint->getDistance($point);
            $this->assertGreaterThanOrEqual($previousDistance, $distance, 'Ensure that shouts are ordered by distance');
            $previousDistance = $distance;
        }
    }
}