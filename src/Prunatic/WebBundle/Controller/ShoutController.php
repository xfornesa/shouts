<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShoutController extends Controller
{
    public function showAction($id)
    {
        // TODO retrieve the shout by id
//        $shout = $this->getDoctrine()
//            ->getRepository('PrunaticWebBundle:Shout')
//            ->find($id);
//        if (!$shout) {
//            throw $this->createNotFoundException('No hem trobat el crit demanat');
//        }
        return $this->render('PrunaticWebBundle:Shout:show.html.twig');
    }

    public function createAction()
    {
        return $this->render('PrunaticWebBundle:Shout:create.html.twig');
    }
}