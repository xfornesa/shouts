<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RankController extends Controller
{
    public function showByCityAction($country, $province, $city)
    {
        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }

    public function showByProvinceAction($country, $province)
    {
        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }

    public function showByCountryAction($country)
    {
        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }
}