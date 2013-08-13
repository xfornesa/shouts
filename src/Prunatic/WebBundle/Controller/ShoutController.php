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
        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->find($id);
        if (!$shout) {
            throw $this->createNotFoundException('No hem trobat el crit demanat');
        }
        return $this->render('PrunaticWebBundle:Shout:show.html.twig', array(
            'shout' => $shout,
        ));
    }

    public function createAction()
    {
        return $this->render('PrunaticWebBundle:Shout:create.html.twig');
    }
}