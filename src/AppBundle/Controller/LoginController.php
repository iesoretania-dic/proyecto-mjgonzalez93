<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends Controller
{
    /**
     * @Route("/entrar", name="usuario_entrar")
     */
    public function entrarAction(AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $ultimoUsuario = $authUtils->getLastUsername();

        return $this->render('seguridad/entrar.html.twig', [
            'ultimo_usuario' => $ultimoUsuario,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/salir", name="usuario_salir")
     */
    public function salirAction()
    {

    }

}
