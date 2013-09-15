<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\City;
use Prunatic\WebBundle\Entity\Province;
use Prunatic\WebBundle\Entity\Country;
use \InvalidArgumentException as InvalidArgumentException;
use Prunatic\WebBundle\Service\NotificationManager;
use \Swift_Mailer as Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ShoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::reportInappropriate
     */
    public function testReportInappropriateOnce()
    {
        $shout = new Shout();
        $ip = $this->getFakeIp();
        $shout->reportInappropriate($ip);

        $this->assertEquals(1, count($shout->getReports()));
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::reportInappropriate
     */
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

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::reportInappropriate
     */
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

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::reportInappropriate
     * @expectedException \Prunatic\WebBundle\Entity\DuplicateException
     */
    public function testThrowsDuplicateExceptionWhenReportInappropriateWithSameIpTwice()
    {
        $shout = new Shout();
        $ip = $this->getFakeIp();
        $shout->reportInappropriate($ip);
        $shout->reportInappropriate($ip);
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::vote
     */
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

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::vote
     */
    public function testTotalVotesIncrement()
    {
        $shout = new Shout();
        $this->assertEquals(0, $shout->getTotalVotes());
        $ip = $this->getFakeIp(1);
        $shout->vote($ip);
        $this->assertEquals(1, $shout->getTotalVotes());
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::removeVote
     */
    public function testTotalVotesDecrement()
    {
        $shout = new Shout();
        $ip = $this->getFakeIp(1);
        $shout->vote($ip);
        $totalVotes = $shout->getTotalVotes();
        $this->assertGreaterThanOrEqual(1, $totalVotes, 'Ensure that total votes is positive when adding a vote');
        $votes = $shout->getVotes();
        $this->assertNotEmpty($votes, 'After adding a vote, shout votes collection should not be empty');
        $vote = $votes->first();
        $shout->removeVote($vote);
        $this->assertEquals($totalVotes-1, $shout->getTotalVotes(), 'Ensure that when removing one vote from a shout collection the total of votes is decreasing by 1');
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::vote
     * @expectedException \Prunatic\WebBundle\Entity\DuplicateException
     */
    public function testThrowsDuplicateExceptionWhenVoteWithSameIpTwice()
    {
        $shout = new Shout();
        $ip = $this->getFakeIp();

        $shout->vote($ip);
        $shout->vote($ip);
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::__construct
     */
    public function testDefaultStatusWhenCreate()
    {
        $shout = new Shout();
        $this->assertEquals(Shout::STATUS_NEW, $shout->getStatus());
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::setStatus
     * @covers \Prunatic\WebBundle\Entity\Shout::getStatus
     */
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

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::setStatus
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsInvalidArgumentExceptionWhenInvalidStatus()
    {
        $shout = new Shout();
        $shout->setStatus('test-invalid');
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::canBeApproved
     */
    public function testCanBeApprovedWithValidStatus()
    {
        $validStatus = array(Shout::STATUS_NEW);
        foreach($validStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->canBeApproved(), sprintf('Shout with status "%s" could be approved', $status));
        }
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::canBeApproved
     */
    public function testCanBeApprovedWithInvalidStatus()
    {
        $invalidStatus = array(Shout::STATUS_APPROVED, Shout::STATUS_INAPPROPRIATE);
        foreach($invalidStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertFalse($shout->canBeApproved(), sprintf('Shout with status "%s" could not be approved', $status));
        }
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::approve
     */
    public function testApprove()
    {
        $shout = new Shout();
        $shout->setStatus(Shout::STATUS_NEW);
        $this->assertEquals(Shout::STATUS_NEW, $shout->getStatus(), sprintf('A shout that could be approved should be in status %s', Shout::STATUS_NEW));
        $shout->approve();
        $this->assertEquals(Shout::STATUS_APPROVED, $shout->getStatus(), sprintf('An approved shout should be in status %s', Shout::STATUS_APPROVED));
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::approve
     * @expectedException \Prunatic\WebBundle\Entity\OperationNotPermittedException
     */
    public function testThrowsOperationNotPermittedExceptionWhenApprovingWithNoValidStatus()
    {
        $shout = new Shout();
        $shout->setStatus(Shout::STATUS_INAPPROPRIATE);
        $this->assertFalse($shout->canBeApproved(), sprintf('A shout with status %s could not be approved', Shout::STATUS_INAPPROPRIATE));
        $shout->approve();
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::isVisible
     */
    public function testIsVisible()
    {
        $visibleStatus = array(Shout::STATUS_APPROVED);
        foreach($visibleStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->isVisible(), sprintf('Shout with status "%s" should be visible', $status));
        }
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::isVisible
     */
    public function testIsNotVisible()
    {
        $noVisibleStatus = array(Shout::STATUS_NEW, Shout::STATUS_INAPPROPRIATE);
        foreach($noVisibleStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertFalse($shout->isVisible(), sprintf('Shout with status "%s" should not be visible', $status));
        }
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::canBeRequestedForRemoval
     */
    public function testCanBeRequestedForRemoval()
    {
        $validStatus = array(Shout::STATUS_NEW, Shout::STATUS_APPROVED, Shout::STATUS_INAPPROPRIATE);
        foreach($validStatus as $status) {
            $shout = new Shout();
            $shout->setStatus($status);
            $this->assertTrue($shout->canBeRequestedForRemoval(), sprintf('Shout with status "%s" should be able to be requested for removal', $status));
        }
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::requestRemoval
     */
    public function testRequestRemoval()
    {
        // Set up mocks for services being used
        $notificationManager = $this->getMockBuilder('\Prunatic\WebBundle\Service\NotificationManager')
            ->disableOriginalConstructor()
            ->setMethods(array('sendShoutRemovalConfirmationEmail'))
            ->getMock();
        $notificationManager->expects($this->atLeastOnce())
            ->method('sendShoutRemovalConfirmationEmail');
        /** @var NotificationManager $notificationManager */

        $router = $this->getMockBuilder('\Symfony\Component\Routing\Generator\UrlGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
        /** @var UrlGenerator $router */

        $shout = new Shout();

        // Check for a change in token field, must not be empty after request removal
        $this->assertEmpty($shout->getToken(), 'The token should be empty before request for removal');
        $shout->requestRemoval($notificationManager, $router);
        $this->assertNotEmpty($shout->getToken(), 'The token should not be empty after request for removal');
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::getProvince
     */
    public function testGetProvince()
    {
        $province = new Province();
        $city = new City();
        $city->setProvince($province);
        $shout = new Shout();
        $shout->setCity($city);

        $this->assertSame($province, $shout->getProvince());
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Shout::getCountry
     */
    public function testGetCountry()
    {
        $country = new Country();
        $province = new Province();
        $province->setCountry($country);
        $city = new City();
        $city->setProvince($province);
        $shout = new Shout();
        $shout->setCity($city);

        $this->assertSame($country, $shout->getCountry());
    }

    public function testTokenUnique()
    {
        $this->markTestSkipped('
            I am not sure how to test this feature.
            ');
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
