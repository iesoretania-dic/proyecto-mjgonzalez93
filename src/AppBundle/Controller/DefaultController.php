<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/", name="inicio")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }
}
