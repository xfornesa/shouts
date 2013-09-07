<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Entity;

use Prunatic\WebBundle\Entity\Point;

class PointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Prunatic\WebBundle\Entity\Point::__construct
     * @covers \Prunatic\WebBundle\Entity\Point::getLongitude
     */
    public function testReturnsLongitude()
    {
        $longitude = 1;
        $latitude = 2;
        $point = new Point($longitude, $latitude);
        $this->assertEquals($longitude, $point->getLongitude());
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Point::__construct
     * @covers \Prunatic\WebBundle\Entity\Point::getLatitude
     */
    public function testReturnsLatitude()
    {
        $longitude = 1;
        $latitude = 2;
        $point = new Point($longitude, $latitude);
        $this->assertEquals($latitude, $point->getLatitude());
    }
}
