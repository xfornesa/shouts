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

    public function reportAction($id)
    {
        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->find($id);
        if (!$shout) {
            throw $this->createNotFoundException('No hem trobat el crit demanat');
        }
        $ip = $this->getRequest()->getClientIp();
        $shout->reportInappropriate($ip);
        $this->getDoctrine()->getManager()->persist($shout);

        if ($this->getRequest()->isXmlHttpRequest()) {

        } else {
            return $this->redirect($this->generateUrl('prunatic_web_homepage'));
        }
    }
}