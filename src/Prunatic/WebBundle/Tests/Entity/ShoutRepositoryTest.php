<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
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
            $shout->setAuthor($author);
            $shout->setEmail('example@example.org');
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
            $shout->setAuthor($author);
            $shout->setEmail('example@example.org');
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
        $baseLatitude = 2.443804;
        $baseLongitude = 41.536691;
        for ($i=0; $i<10; $i++) {
            $shout = new Shout();
            $shout
                ->setAuthor('Example author')
                ->setEmail('example@example.org')
                ->setLatitude($baseLatitude * rand(0.001, 0.009))
                ->setLongitude($baseLongitude * rand(0.001, 0.009))
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
            ->getNearbyVisibleShouts($baseLatitude, $baseLongitude)
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
            $currentDistance = $this->distance($baseLatitude, $baseLongitude, $shout->getLatitude(), $shout->getLongitude());
            $this->assertLessThanOrEqual($previousDistance, $currentDistance, 'Ensure that shouts are ordered by distance');
            $previousDistance = $currentDistance;
        }

    }

    /**
     * Calculates the distance between two coordinates
     *
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @return float
     */
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);

        return $dist;
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