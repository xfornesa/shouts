<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;


class Point
{
    const DISTANCE_HAVERSINE = 'haversine';

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct($longitude, $latitude)
    {
        $this->longitude = $longitude;
        $this->latitude  = $latitude;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Calculates the distance (in kilometers) to a point
     *
     * @param Point $point
     * @return float
     */
    public function getDistance(Point $point, $type = Point::DISTANCE_HAVERSINE)
    {
        $distance = null;
        switch ($type) {
            case Point::DISTANCE_HAVERSINE:
            default:
                $distance = $this->getDistanceHaversine($point);
                break;
        }

        return $distance;
    }

    /**
     * Calculates the distance haversine (in kilometers) to a point
     *
     * @param Point $point
     * @return float
     */
    private function getDistanceHaversine(Point $point)
    {
        $lat1 = $this->getLatitude();
        $lon1 = $this->getLongitude();
        $lat2 = $point->getLatitude();
        $lon2 = $point->getLongitude();

        $theta = $lon1 - $lon2;
        $distance = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distanceInMiles = $distance * 60 * 1.1515;
        $distanceInKilometers = $distanceInMiles * 1.609344;

        return $distanceInKilometers;
    }


}