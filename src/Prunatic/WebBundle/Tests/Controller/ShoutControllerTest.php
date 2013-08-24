<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Controller;

use Prunatic\WebBundle\Entity\Shout;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;

class ShoutControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $this->markTestIncomplete('TODO functional test of creating a shout');
    }

    public function testCreateValidations()
    {
        $this->markTestIncomplete('TODO functional test of creating a shout without completing all fields in the form');
    }

    public function testShowWhenIsVisible()
    {
        $client = static::createClient();

        // TODO extract common mock definitions to methods
        // First, set up a shout mock
        $shoutId = 1;
        $shout = $this->getMockBuilder('\Prunatic\WebBundle\Entity\Shout')
            ->setMethods(array('getId', 'getAuthor', 'getEmail', 'getMessage', 'getLongitude', 'getLatitude', 'isVisible'))
            ->getMock();
        $shout->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($shoutId));
        $shout->expects($this->any())
            ->method('getAuthor')
            ->will($this->returnValue('Author 1'));
        $shout->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('email.1@elmeucrit.cat'));
        $shout->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue('Catalunya is not Spain'));
        $shout->expects($this->any())
            ->method('getLongitude')
            ->will($this->returnValue(41,32900755));
        $shout->expects($this->any())
            ->method('getLatitude')
            ->will($this->returnValue(2,42669737));
        $shout->expects($this->any())
            ->method('isVisible')
            ->will($this->returnValue(true));

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find', 'getNearbyVisibleShouts'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($shout));
        $shoutRepository->expects($this->any())
            ->method('getNearbyVisibleShouts')
            ->will($this->returnValue(array()));

        // Last, mock the EntityManager to return the mock of the repository
        $factory = $this->getMock('\Doctrine\ORM\Mapping\ClassMetadataFactory', array('getLoadedMetadata'));
        $factory->expects($this->any())
            ->method('getLoadedMetadata')
            ->will($this->returnValue(array()));
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
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

        $url = $client->getContainer()->get('router')->generate('prunatic_shout_show', array('id' => $shoutId));
        $client->request('GET', $url);

        // Assert that the response status code is 2XX
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Ensure that HTTP status code is 2XX');

    }

    public function testShowWhenIsNotVisible()
    {
        $client = static::createClient();

        // TODO extract common mock definitions to methods
        // First, set up a shout mock
        $shoutId = 1;
        $shout = $this->getMockBuilder('\Prunatic\WebBundle\Entity\Shout')
            ->setMethods(array('isVisible'))
            ->getMock();
        $shout->expects($this->any())
            ->method('isVisible')
            ->will($this->returnValue(false));

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($shout));

        // Last, mock the EntityManager to return the mock of the repository
        $factory = $this->getMock('\Doctrine\ORM\Mapping\ClassMetadataFactory', array('getLoadedMetadata'));
        $factory->expects($this->any())
            ->method('getLoadedMetadata')
            ->will($this->returnValue(array()));
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
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

        $url = $client->getContainer()->get('router')->generate('prunatic_shout_show', array('id' => $shoutId));
        $client->request('GET', $url);

        // Assert that the response status code is 404
        $this->assertTrue($client->getResponse()->isNotFound(), 'Ensure that HTTP status code is 404');
    }

    public function testShowWithNonExistentShout()
    {
        $client = static::createClient();

        // TODO extract common mock definitions to methods
        // First, set up a shout mock
        $shoutId = 1;
        $shout = null;

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($shout));

        // Last, mock the EntityManager to return the mock of the repository
        $factory = $this->getMock('\Doctrine\ORM\Mapping\ClassMetadataFactory', array('getLoadedMetadata'));
        $factory->expects($this->any())
            ->method('getLoadedMetadata')
            ->will($this->returnValue(array()));
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
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

        $url = $client->getContainer()->get('router')->generate('prunatic_shout_show', array('id' => $shoutId));
        $client->request('GET', $url);

        // Assert that the response status code is 404
        $this->assertTrue($client->getResponse()->isNotFound(), 'Ensure that HTTP status code is 404');
    }

    public function testRequestRemoval()
    {
        $client = static::createClient();
        // Enable the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();
        $this->assertTrue($client->getContainer()->has('profiler'), 'Ensure that framework.profiler.enabled = true in config_test.yml file');
        $this->assertFalse($client->getContainer()->getParameter('profiler_listener.only_exceptions'), 'Ensure that framework.profiler.only_exceptions = false in config_test.yml file');

        // TODO extract common mock definitions to methods
        // First, set up a shout mock
        $shoutId = 1;
        $shoutEmail = 'email@example.org';
        $shoutAuthor = 'Example author';
        $shoutToken = $this->generateToken();
        $shout = $this->getMockBuilder('\Prunatic\WebBundle\Entity\Shout')
            ->setMethods(array('getId', 'setToken', 'getEmail', 'getAuthor', 'getToken', 'generateToken'))
            ->getMock();
        $shout->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($shoutId));
        $shout->expects($this->once())
            ->method('setToken');
        $shout->expects($this->atLeastOnce())
            ->method('getEmail')
            ->will($this->returnValue($shoutEmail));
        $shout->expects($this->any())
            ->method('getAuthor')
            ->will($this->returnValue($shoutAuthor));
        $shout->expects($this->atLeastOnce())
            ->method('getToken')
            ->will($this->returnValue($shoutToken));

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('find')
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
        $client->request('POST', $url, array('id' => $shoutId));


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

        // Assert that the response status code is 200
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Ensure that HTTP status code is 200');
    }

    public function testRequestRemovalForNonExistentShout()
    {
        $this->markTestIncomplete('TODO when request for removal for a shout that does not exist, error 404');
    }

    public function testRequestRemovalWithShoutThatCanNotBeRequestedForRemoval()
    {
        $client = static::createClient();

        // TODO extract common mock definitions to methods
        // First, set up a shout mock
        $shoutId = 1;
        $shout = $this->getMockBuilder('\Prunatic\WebBundle\Entity\Shout')
            ->setMethods(array('getStatus'))
            ->getMock();
        $shout->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue('invalid'));

        // Now, mock the repository so it returns the mock of the shout
        $shoutRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find'))
            ->getMock();
        $shoutRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($shout));

        // Last, mock the EntityManager to return the mock of the repository
        $factory = $this->getMock('\Doctrine\ORM\Mapping\ClassMetadataFactory', array('getLoadedMetadata'));
        $factory->expects($this->any())
            ->method('getLoadedMetadata')
            ->will($this->returnValue(array()));
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
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
        $client->request('POST', $url, array('id' => $shoutId));

        // Assert that the response status code is 500
        $this->assertTrue($client->getResponse()->isServerError(), 'Ensure that HTTP status code is 500');
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
