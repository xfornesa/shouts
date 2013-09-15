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
        $em = $this->getDoctrine()->getManager()->getRepository('PrunaticWebBundle:City');
        $city = $em->findBySlugWithProvinceAndCountry($city, $province, $country);
        if (!$city) {
            $this->createNotFoundException();
        }

        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }

    public function showByProvinceAction($country, $province)
    {
        $em = $this->getDoctrine()->getManager()->getRepository('PrunaticWebBundle:Province');
        $province = $em->findBySlugWithCountry($province, $country);
        if (!$province) {
            $this->createNotFoundException();
        }

        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }

    public function showByCountryAction($country)
    {
        $em = $this->getDoctrine()->getManager()->getRepository('PrunaticWebBundle:Country');
        $country = $em->findBySlug($country);
        if (!$country) {
            $this->createNotFoundException();
        }

        return new \Symfony\Component\HttpFoundation\Response('<html><body>TO DO</body></html>');
    }
}