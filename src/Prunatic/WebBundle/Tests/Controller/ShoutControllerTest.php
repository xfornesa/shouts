<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Controller;

use Prunatic\WebBundle\Entity\Shout;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\Validator\Constraints\True;

class ShoutControllerTest extends WebTestCase
{
    public function testShowAShoutVisible()
    {
        $this->markTestIncomplete('TODO functional test when browse detail for a shout in a status new or approved, get a 200 http code');
    }

    public function testShowAShoutInappropriate()
    {
        $this->markTestIncomplete('TODO functional test when browse detail for a shout in a inappropriate, get a 404 http code');
    }

    public function testRequestRemoval()
    {
        $client = static::createClient();
        // Enable the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();
        $this->assertTrue($client->getContainer()->has('profiler'), 'Ensure that framework.profiler.enabled = true in config_test.yml file');
        $this->assertFalse($client->getContainer()->getParameter('profiler_listener.only_exceptions'), 'Ensure that framework.profiler.only_exceptions = false in config_test.yml file');

        // First, set up a shout mock
        $shoutId = 1;
        $shoutEmail = 'email@example.org';
        $shoutToken = $this->generateToken();
        $shout = $this->getMockBuilder('\Prunatic\WebBundle\Entity\Shout')
            ->setMethods(array('getId', 'setToken', 'getEmail', 'getToken'))
            ->getMock();
        $shout->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($shoutId));
        $shout->expects($this->once())
            ->method('setToken');
        $shout->expects($this->atLeastOnce())
            ->method('getEmail')
            ->will($this->returnValue($shoutEmail));
        $shout->expects($this->atLeastOnce())
            ->method('getToken')
            ->will($this->returnValue($shoutToken));

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository =
            $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($shout));

        // Last, mock the EntityManager to return the mock of the repository
        $factory = $this->getMock('\Doctrine\ORM\Mapping\ClassMetadataFactory', array('getLoadedMetadata'));
        $factory->expects($this->any())
            ->method('getLoadedMetadata')
            ->will($this->returnValue(array()));
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('persist', 'remove', 'flush', 'getRepository', 'getMetadataFactory'))
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($factory));

        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with('PrunaticWebBundle:Shout')
            ->will($this->returnValue($shoutRepository));

        // inject our mock as a entity manager
        $client->getContainer()->set('doctrine.orm.default_entity_manager', $entityManager);

        // post to url
        $url = $client->getContainer()->get('router')->generate('prunatic_shout_remove');
        $crawler = $client->request('POST', $url, array('id' => $shoutId));


        // assert email was being sent
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount(), 'No email has been sent.');

        // Asserting e-mail data
        $collectedMessages = $mailCollector->getMessages('default');
        /** @var \Swift_Message $message */
        $message = reset($collectedMessages);
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertArrayHasKey($shoutEmail, $message->getTo(), 'The destination email does not correspond with shout email.');
        $this->assertContains($shoutToken, $message->getBody(), 'The token is not present on message body.');
    }

    public function testConfirmRemoval()
    {
        $this->markTestIncomplete('TODO complete confirm removal action for shout controller');
    }

    /**
     * Generates a token
     *
     * @return string
     */
    private function generateToken()
    {
        return md5(uniqid(rand(), true));
    }
}
