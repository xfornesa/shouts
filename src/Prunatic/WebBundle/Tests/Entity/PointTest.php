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

    /**
     * @covers \Prunatic\WebBundle\Entity\Point::getDistance
     *
     * @param $pointA
     * @param $pointB
     * @param $pointC
     *
     * @dataProvider distanceHaversineProvider
     */
    public function testDistanceHaversineRelation($pointA, $pointB, $pointC)
    {
        $distanceFromAToB = $pointA->getDistance($pointB, Point::DISTANCE_HAVERSINE);
        $distanceFromAToC = $pointA->getDistance($pointC, Point::DISTANCE_HAVERSINE);
        $distanceFromBToC = $pointB->getDistance($pointC, Point::DISTANCE_HAVERSINE);

        $this->assertGreaterThan(0, $distanceFromAToB);
        $this->assertGreaterThan($distanceFromAToB, $distanceFromAToC);
        $this->assertGreaterThan($distanceFromBToC, $distanceFromAToC);
    }

    public function distanceHaversineProvider()
    {
        $examples = array();
        $examples[] = array(
            new Point(1, 10),
            new Point(1, 20),
            new Point(1, 30)
        );
        $examples[] = array(
            new Point(1, 10),
            new Point(5, 10),
            new Point(10, 10)
        );

        return $examples;
    }

    /**
     * @covers \Prunatic\WebBundle\Entity\Point::getDistance
     * @see http://www.movable-type.co.uk/scripts/latlong.html
     *
     * @param $pointA
     * @param $pointB
     * @param $expectedDistance
     *
     * @dataProvider distanceHaversineCalculationProvider
     */
    public function testDistanceHaversineCalculation($pointA, $pointB, $expectedDistance)
    {
        $distance = $pointA->getDistance($pointB, Point::DISTANCE_HAVERSINE);

        $this->assertEquals($expectedDistance, $distance, null, 0.2);
    }

    public function distanceHaversineCalculationProvider()
    {
        $examples = array();
        $examples[] = array(
            new Point(41.661301, 2.436473),
            new Point(41.578228, 2.44136),
            9.246
        );

        return $examples;
    }
}
