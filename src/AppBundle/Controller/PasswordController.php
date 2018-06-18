<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class PasswordController extends Controller
{

    /**
     * @Route("/password/", name="password_propia")
     * @Route("/editar/password/{id}", name="password_usuario")
     * @IsGranted("ROLE_ADMIN")
     */
    public function cambiarPassAction(Request $request, User $id = null){
        $em = $this->getDoctrine()->getManager();

        if (null === $id) {
            $usuario = $this->getUser();
            $el_mismo = true;
        }else{
            $usuario = $this->getDoctrine()->getRepository('AppBundle:User')->usuarioPassword($id);
            $el_mismo = false;
        }

        $form = $this->createForm(UserType::class, $usuario,['el_mismo' => $el_mismo ,'cambiar_pass' => true]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            try {
                $claveFormulario = $form->get('nueva')->get('first')->getData();
                if ($claveFormulario) {
                    $clave = $this->get('security.password_encoder')
                        ->encodePassword($usuario, $claveFormulario);
                    $usuario->setPassword($clave);
                }
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ' );
                return $this->redirectToRoute('inicio');
            }catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ' );
            }
        }
        return $this->render('usuarios/password.html.twig', [
            'formulario' => $form->createView()
        ]);
    }

}
