<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Entity\DuplicateIpException;
use Prunatic\WebBundle\Entity\Shout;
use InvalidArgumentException;

class ShoutTest extends \PHPUnit_Framework_TestCase
{
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

    public function testThrowsDuplicateIpExceptionWhenReportInappropriateWithSameIpTwice()
    {
        try {
            $shout = new Shout();
            $ip = $this->getFakeIp();
            $shout->reportInappropriate($ip);
            $shout->reportInappropriate($ip);
        } catch (DuplicateIpException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised.');
    }

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

    public function testThrowsDuplicateIpExceptionWhenVoteWithSameIpTwice()
    {
        try {
            $shout = new Shout();
            $ip = $this->getFakeIp();

            $shout->vote($ip);
            $shout->vote($ip);
        } catch (DuplicateIpException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised.');
    }

    public function testValidStatus()
    {
        //$this->
    }

    public function testInvalidStatus()
    {
        try {
            $shout = new Shout();
            $shout->setStatus('test-invalid');
        } catch (InvalidArgumentException $e) {
            return;
        }
        $this->fail('An expected Exception has not been raised.');
    }

    private function getFakeIp($i = 1)
    {
        return sprintf('127.0.0.%s', $i);
    }
}
