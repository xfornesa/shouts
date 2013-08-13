<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PrunaticWebBundle:Default:index.html.twig');
    }

    public function legalAction()
    {
        return $this->render('PrunaticWebBundle:Default:legal.html.twig');
    }

    public function faqAction()
    {
        return $this->render('PrunaticWebBundle:Default:faq.html.twig');
    }
}
