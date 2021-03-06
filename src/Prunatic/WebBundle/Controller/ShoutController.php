<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Prunatic\WebBundle\Entity\DuplicateException;
use Prunatic\WebBundle\Entity\OperationNotPermittedException;
use Prunatic\WebBundle\Entity\Shout;
use Prunatic\WebBundle\Entity\ShoutRepository;
use Prunatic\WebBundle\Entity\Vote;
use Prunatic\WebBundle\Service\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    public function createAction(Request $request)
    {
        $shout = new Shout();
        $form = $this->createFormBuilder($shout)
            ->add('author')
            ->add('email', 'email')
            ->add('message')
            ->add('image', 'file', array(
                'required' => false,
                'data_class' => null,
                'mapped' => true
                )
            )
            ->add('latitude')
            ->add('longitude')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            // We get the uploadable manager!
            $uploadableManager = $this->container->get('stof_doctrine_extensions.uploadable.manager');
            $uploadableManager->markEntityToUpload($shout, $shout->getImage());
            $shout->approve();
            $this->getDoctrine()->getManager()->persist($shout);
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                "S'ha afegit el crit correctament"
            );

            return $this->redirect($this->generateUrl('prunatic_shout_show', array('id' => $shout->getId())));
        }

        return $this->render('PrunaticWebBundle:Shout:create.html.twig', array(
            'form' => $form->createView(),
        ));
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
        /** @var NotificationManager $notificationManager */
        $notificationManager = $this->get('prunatic_web.notification_manager');
        /** @var UrlGeneratorInterface $router */
        $router = $this->get('router');
        try {
            $shout->requestRemoval($notificationManager, $router);
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
        $shouts = $this->getShoutRepository()->getNewestVisibleShouts()
        ;

        return $this->render('PrunaticWebBundle:Shout:components/newest.html.twig', array(
            'shouts' => $shouts
        ));
    }

    public function topRatedAction()
    {
        $shouts = $this->getShoutRepository()->getTopRatedVisibleShouts(0, 5)
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

        return $this->render('PrunaticWebBundle:Shout:components/showMap.html.twig', array(
            'shout' => $shout,
        ));
    }

    public function nearbyShoutsAction($id)
    {
        $shout = $this->getShoutByIdOrNotFoundException($id);
        if (!$shout->isVisible()) {
            throw $this->createNotFoundException(sprintf("El crit demanat amb id %s no està disponible", $id));
        }

        $shouts = $this->getShoutRepository()->getNearbyVisibleShouts($shout->getPoint(), 0, 12)
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
        $shout = $this->getShoutRepository()->find($id);
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
        $shout = $this->getShoutRepository()->findOneByToken($token);
        if (!$shout) {
            throw $this->createNotFoundException(sprintf('No hem trobat el crit demanat amd el token %s', $token));
        }

        return $shout;
    }

    /**
     * @return ShoutRepository
     */
    private function getShoutRepository()
    {
        return $this->getDoctrine()->getRepository('PrunaticWebBundle:Shout');
    }
}