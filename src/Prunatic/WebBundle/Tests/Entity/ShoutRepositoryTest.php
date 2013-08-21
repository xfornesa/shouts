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
        for ($i=0; $i <= 10; $i++) {
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
        for ($i=0; $i <= 10; $i++) {
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

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}