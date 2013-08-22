<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Prunatic\WebBundle\Entity\DuplicateException;
use Prunatic\WebBundle\Entity\OperationNotPermittedException;
use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\Vote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShoutController extends Controller
{
    public function showAction($id)
    {
        $shout = $this->getShoutByIdOrNotFoundException($id);
        if (!$shout->isVisible()) {
            throw $this->createNotFoundException(sprintf("El crit demanat amb id %s no està disponible", $id));
        }

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
        $shout = $this->getShoutByIdOrNotFoundException($id);

        $ip = $request->getClientIp();
        try {
            $shout->reportInappropriate($ip);
        } catch (DuplicateException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }

        $this->getDoctrine()->getManager()->persist($shout);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            $response->setData(true);

            return $response;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            sprintf("El crit amb id %s s'ha reportat com a inapropiat. Gràcies per la teva col·laboració.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    public function voteAction(Request $request)
    {
        $id = $request->get('id');
        $shout = $this->getShoutByIdOrNotFoundException($id);
        if (!$shout->isVisible()) {
            throw $this->createNotFoundException(sprintf("El crit demanat amb id %s no està disponible", $id));
        }

        $ip = $request->getClientIp();
        try {
            $vote = new Vote($ip);
            $shout->addVote($vote);
        } catch (DuplicateException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }

        $this->getDoctrine()->getManager()->persist($shout);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            $response->setData(true);

            return $response;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            sprintf("Gràcies per recolçar el crit amb id %s.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    public function requestRemovalAction(Request $request)
    {
        $id = $request->get('id');
        $shout = $this->getShoutByIdOrNotFoundException($id);

        // prepare required info
        $mailer = $this->get('mailer');
        $router = $this->get('router');
        try {
            $shout->requestRemoval($mailer, $router);
        } catch (OperationNotPermittedException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }

        // store shout updates
        $this->getDoctrine()->getManager()->persist($shout);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            $response->setData(true);

            return $response;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            sprintf("S'ha enviat un email de confirmació d'esborrat a l'autor del crit amb id %s.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    public function confirmRemovalAction($token)
    {
        $shout = $this->getShoutByTokenOrNotFoundException($token);
        $id = $shout->getId();
        $this->getDoctrine()->getManager()->remove($shout);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            sprintf("S'ha silenciat el crit amb id %s.", $id)
        );

        return $this->redirect($this->generateUrl('prunatic_web_homepage'));
    }

    public function newestAction()
    {
        $shouts = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->getNewestVisibleShouts()
        ;

        return $this->render('PrunaticWebBundle:Shout:components/newest.html.twig', array(
            'shouts' => $shouts
        ));
    }

    public function topRatedAction()
    {
        $shouts = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->getTopRatedVisibleShouts()
        ;

        return $this->render('PrunaticWebBundle:Shout:components/topRated.html.twig', array(
            'shouts' => $shouts
        ));
    }

    public function showMapAction($id)
    {
        $shout = $this->getShoutByIdOrNotFoundException($id);
        if (!$shout->isVisible()) {
            throw $this->createNotFoundException(sprintf("El crit demanat amb id %s no està disponible", $id));
        }

        $latitude = $shout->getLatitude();
        $longitude = $shout->getLongitude();
        $shouts = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->getNearbyVisibleShouts($latitude, $longitude)
        ;

        return $this->render('PrunaticWebBundle:Shout:components/showMap.html.twig', array(
            'shout' => $shout,
            'shouts' => $shouts,
        ));
    }

    public function nearbyShoutsAction($id)
    {
        $shout = $this->getShoutByIdOrNotFoundException($id);
        if (!$shout->isVisible()) {
            throw $this->createNotFoundException(sprintf("El crit demanat amb id %s no està disponible", $id));
        }

        $latitude = $shout->getLatitude();
        $longitude = $shout->getLongitude();
        $shouts = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->getNearbyVisibleShouts($latitude, $longitude)
        ;

        return $this->render('PrunaticWebBundle:Shout:components/nearbyShouts.html.twig', array(
            'shout' => $shout,
            'shouts' => $shouts,
        ));
    }

    /**
     * Find a shout by id and status (is visible)
     *
     * @param $id
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Shout
     */
    private function getShoutByIdOrNotFoundException($id)
    {
        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->find($id);
        if (!$shout) {
            throw $this->createNotFoundException(sprintf("No hem trobat el crit demanat amb l'id %s", $id));
        }

        return $shout;
    }

    /**
     * Find a shout by token
     *
     * @param string $token
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Shout
     */
    private function getShoutByTokenOrNotFoundException($token)
    {
        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->findOneByToken($token);
        if (!$shout) {
            throw $this->createNotFoundException(sprintf('No hem trobat el crit demanat amd el token %s', $token));
        }

        return $shout;
    }
}