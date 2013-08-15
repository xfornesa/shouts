<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\Vote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShoutController extends Controller
{
    public function showAction($id)
    {
        $shout = $this->getShoutByIdOrNotFoundException($id, true);

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
        $shout = $this->getShoutByIdOrNotFoundException($id, false);

        $ip = $request->getClientIp();
        $shout->reportInappropriate($ip);

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
        $shout = $this->getShoutByIdOrNotFoundException($id, true);

        $ip = $request->getClientIp();
        $vote = new Vote($ip);
        $shout->addVote($vote);

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
        $shout = $this->getShoutByIdOrNotFoundException($id, false);

        // set token
        $token = $this->generateToken();
        $shout->setToken($token);

        // send email confirmation
        $confirmUrl = $this->generateUrl('prunatic_shout_confirm_remove', array('token' => $shout->getToken()), true);
        $mailer = $this->get('mailer');
        $shout->sendRemovalConfirmationEmail($mailer, $confirmUrl);

        // store updates
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

    /**
     * Find a shout by id and status (is visible)
     *
     * @param $id
     * @param bool $onlyVisible
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Shout
     */
    private function getShoutByIdOrNotFoundException($id, $onlyVisible = true)
    {
        $params = array(
            'id' => $id
        );
        if ($onlyVisible) {
            $params['status'] = Shout::STATUS_APPROVED;
        }

        $shout = $this->getDoctrine()
            ->getRepository('PrunaticWebBundle:Shout')
            ->findOneBy($params);
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

    /**
     * Generates a token
     *
     * @return string
     */
    private function generateToken()
    {
        return md5(uniqid(rand(), true));
    }
}