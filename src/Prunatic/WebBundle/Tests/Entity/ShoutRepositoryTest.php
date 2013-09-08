<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Prunatic\WebBundle\Entity\Point;
use Prunatic\WebBundle\Entity\Shout;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

class ShoutRepositoryTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    private $application;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        // init application to be able to run console commands
        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);

        // clear test database
        $this->runConsole("doctrine:schema:drop", array("--force" => true));
        $this->runConsole("doctrine:schema:create");
        //$this->runConsole("doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../DataFixtures"));

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    /**
     * Use application object to run console commands
     *
     * @param $command
     * @param array $options
     * @return mixed
     */
    protected function runConsole($command, Array $options = array())
    {
        $options["--env"] = "test";
        $options["-q"] = null;
        $options = array_merge($options, array('command' => $command));
        return $this->application->run(new ArrayInput($options));
    }

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

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}