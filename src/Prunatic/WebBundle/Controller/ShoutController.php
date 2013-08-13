<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\Vote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShoutController extends Controller
{
    public function showAction($id)
    {
        $shout = $this->getShoutOrNotFoundException($id);

        return $this->render('PrunaticWebBundle:Shout:show.html.twig', array(
            'shout' => $shout,
        ));
    }

    public function createAction()
    {
        return $this->render('PrunaticWebBundle:Shout:create.html.twig');
    }

    public function reportAction(Request $request)
    {
        $id = $request->get('id');
        $shout = $this->getShoutOrNotFoundException($id);

        $ip = $request->getClientIp();
        $shout->reportInappropriate($ip);

        $this->getDoctrine()->getManager()->persist($shout);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(true));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            sprintf("El crit amb id %s s'ha reportat com a inapropiat. Gràcies per la teva col·laboració.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    public function voteAction(Request $request)
    {
        $id = $request->get('id');
        $shout = $this->getShoutOrNotFoundException($id);

        $ip = $request->getClientIp();
        $vote = new Vote($ip);
        $shout->addVote($vote);

        $this->getDoctrine()->getManager()->persist($shout);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(true));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            sprintf("Gràcies per recolçar el crit amb id %s.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    /**
     * @param $id
     * @return Shout
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getShoutOrNotFoundException($id)
    {
        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->find($id);
        if (!$shout) {
            throw $this->createNotFoundException('No hem trobat el crit demanat');
        }

        return $shout;
    }
}