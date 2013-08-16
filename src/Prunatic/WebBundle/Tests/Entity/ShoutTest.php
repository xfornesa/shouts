<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Entity\DuplicateException;
use Prunatic\WebBundle\Entity\OperationNotPermittedException;
use Prunatic\WebBundle\Entity\Shout;
use \InvalidArgumentException as InvalidArgumentException;
use \Swift_Mailer as Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ShoutTest extends \PHPUnit_Framework_TestCase
{
    // report issues
    public function testReportInappropriateOnce()
    {
        $shout = new Shout();
        $ip = $this->getFakeIp();
        $shout->reportInappropriate($ip);

        $this->assertEquals(1, count($shout->getReports()));
    }

    public function testReportInappropriateWithoutStatusChange()
    {
        $shout = new Shout();
        $status = $shout->getStatus();
        for ($i=1; $i<Shout::MIN_REPORTS; $i++) {
            $ip = $this->getFakeIp($i);
            $shout->reportInappropriate($ip);
        }

        $this->assertEquals($status, $shout->getStatus());
    }

    public function testReportInappropriateWithStatusChange()
    {
        $shout = new Shout();
        $status = $shout->getStatus();
        for ($i=0; $i<=Shout::MIN_REPORTS; $i++) {
            $ip = $this->getFakeIp($i);
            $shout->reportInappropriate($ip);
        }

        $this->assertNotEquals($status, $shout->getStatus());
    }

    public function testThrowsDuplicateExceptionWhenReportInappropriateWithSameIpTwice()
    {
        try {
            $shout = new Shout();
            $ip = $this->getFakeIp();
            $shout->reportInappropriate($ip);
            $shout->reportInappropriate($ip);
        } catch (DuplicateException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised');
    }

    // votes issues
    public function testAddManyVotes()
    {
        $shout = new Shout();
        $expectedVotes = rand(1, 10);
        for($i=1; $i<=$expectedVotes; $i++) {
            $ip = $this->getFakeIp($i);
            $shout->vote($ip);
        }

        $this->assertEquals($expectedVotes, count($shout->getVotes()));
    }

    public function testThrowsDuplicateExceptionWhenVoteWithSameIpTwice()
    {
        try {
            $shout = new Shout();
            $ip = $this->getFakeIp();

            $shout->vote($ip);
            $shout->vote($ip);
        } catch (DuplicateException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised');
    }

    // status issues
    public function testDefaultStatusWhenCreate()
    {
        $shout = new Shout();
        $this->assertEquals(Shout::STATUS_NEW, $shout->getStatus());
    }

    public function testAcceptOnlyValidStatus()
    {
        $availableStatus = array(
            Shout::STATUS_NEW,
            Shout::STATUS_APPROVED,
            Shout::STATUS_INAPPROPRIATE
        );
        $shout = new Shout();
        foreach ($availableStatus as $status) {
            $shout->setStatus($status);
            $this->assertEquals($status, $shout->getStatus());
        }
    }

    public function testThrowsInvalidArgumentExceptionWhenInvalidStatus()
    {
        try {
            $shout = new Shout();
            $shout->setStatus('test-invalid');
        } catch (InvalidArgumentException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised');
    }

    public function testCanBeApprovedWithValidStatus()
    {
        $validStatus = array(Shout::STATUS_NEW);
        foreach($validStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->canBeApproved(), sprintf('Shout with status "%s" could be approved', $status));
        }
    }

    public function testCanBeApprovedWithInvalidStatus()
    {
        $invalidStatus = array(Shout::STATUS_APPROVED, Shout::STATUS_INAPPROPRIATE);
        foreach($invalidStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertFalse($shout->canBeApproved(), sprintf('Shout with status "%s" could not be approved', $status));
        }
    }

    public function testApprove()
    {
        $shout = new Shout();
        $shout->setStatus(Shout::STATUS_NEW);
        $this->assertEquals(Shout::STATUS_NEW, $shout->getStatus(), sprintf('A shout that could be approved should be in status %s', Shout::STATUS_NEW));
        $shout->approve();
        $this->assertEquals(Shout::STATUS_APPROVED, $shout->getStatus(), sprintf('An approved shout should be in status %s', Shout::STATUS_APPROVED));
    }

    public function testThrowsOperationNotPermittedExceptionWhenApprovingWithNoValidStatus()
    {
        try {
            $shout = new Shout();
            $shout->setStatus(Shout::STATUS_INAPPROPRIATE);
            $this->assertFalse($shout->canBeApproved(), sprintf('A shout with status %s could not be approved', Shout::STATUS_INAPPROPRIATE));
            $shout->approve();
        } catch (OperationNotPermittedException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised');
    }

    public function testIsVisible()
    {
        $visibleStatus = array(Shout::STATUS_APPROVED);
        foreach($visibleStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->isVisible(), sprintf('Shout with status "%s" should be visible', $status));
        }
    }

    public function testIsNotVisible()
    {
        $noVisibleStatus = array(Shout::STATUS_NEW, Shout::STATUS_INAPPROPRIATE);
        foreach($noVisibleStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertFalse($shout->isVisible(), sprintf('Shout with status "%s" should not be visible', $status));
        }
    }

    public function testCanBeRequestedForRemoval()
    {
        $validStatus = array(Shout::STATUS_NEW, Shout::STATUS_APPROVED, Shout::STATUS_INAPPROPRIATE);
        foreach($validStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->canBeRequestedForRemoval(), sprintf('Shout with status "%s" should be able to be requested for removal', $status));
        }
    }

    // Removal issues
    public function testRequestRemoval()
    {
        // Set up mocks for services being used
        $mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
            ->getMock();
        $mailer->expects($this->atLeastOnce())
            ->method('send');

        $router = $this->getMockBuilder('\Symfony\Component\Routing\Generator\UrlGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();

        /** @var Shout $shout */
        $shout = new Shout();

        // Check for a change in token field, must not be empty after request removal
        $this->assertEmpty($shout->getToken(), 'The token should be empty before request for removal');
        $shout->requestRemoval($mailer, $router);
        $this->assertNotEmpty($shout->getToken(), 'The token should not be empty after request for removal');
    }

    // token issues
    public function testTokenUnique()
    {
        $this->markTestSkipped('
            Is really necessary to ensure that a token is unique?
            Probabilistic it should work, and there will be not enough pending removal request to have collisions.
            ');
    }

    // helpers
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
