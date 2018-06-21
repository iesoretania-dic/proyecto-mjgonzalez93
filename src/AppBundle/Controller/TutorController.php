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

class TutorController extends Controller
{

    /**
     * @Route("/tutores", name="listado_tutores")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listadoTutoresAction()
    {

        $conexion = mysqli_connect('localhost', 'root', 'oretania', 'atica_fct');
        if (!$conexion) {
            die('No se puede conectar: ' . mysqli_error($conexion));
        }

        $sentencia = "SELECT * FROM `tutorized_groups` ";
        $resultado = $conexion->query($sentencia);
        $tutorized_group = [];

        while ($tupla = mysqli_fetch_assoc($resultado)) {
            $tutorized_group[] = $tupla;
        }

        foreach ($tutorized_group as $grupo){
            $tutores[] = $this->getDoctrine()->getRepository('AppBundle:User')->listadoTutores($grupo['user_id']);
        }

        return $this->render('tutores/listado.html.twig', [
            'tutores' => $tutores
        ]);
    }

    /**
     * @Route("/nuevo/tutor/", name="creacion_tutores")
     * @Route("/editar/tutor/{id}", name="edicion_tutores")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioAlumnosAction(Request $request, User $usuario = null){
        $em = $this->getDoctrine()->getManager();
        $nuevo = false;
        $modificar_perfil = true;
        $tutores = true;

        if (null === $usuario) {
            $usuario = new User();
            $nuevo = true;
            $modificar_perfil = false;
            $em->persist($usuario);
        }

        $form = $this->createForm(UserType::class, $usuario,['tutor' => $tutores, 'modificar_perfil' => $modificar_perfil ,'nuevo' => $nuevo]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                if($nuevo == true){
                    $dni = $form->get('reference')->getData();
                    $usuario->setLoginUsername($dni);
                    $usuario->setGlobalAdministrator(false);
                    $usuario->setAllowExternalLogin(false);
                    $usuario->setFinancialManager(false);
                    $usuario->setEnabled(false);
                    $clave = $this->get('security.password_encoder')->encodePassword($usuario, $dni);
                    $usuario->setPassword($clave);

                }
                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ' );
                return $this->redirectToRoute('listado_tutores');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('tutores/usuario.html.twig', [
            'usuario' => $usuario,
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/eliminar/tutor/{id}", name="eliminar_tutor")
     * @IsGranted("ROLE_ADMIN")
     */
    public function eliminarTutorAction(Request $request, User $tutor){

        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            try {

                $acuerdos = $this->getDoctrine()->getRepository('AppBundle:Agreement')->listadoAcuerdosTutor($tutor);

                foreach($acuerdos as $acuerdo) {
                    $em->remove($acuerdo);
                };

                $em->remove($tutor);
                $em->flush();
                $this->addFlash('exito', 'Tutor eliminado con exito');
                return $this->redirectToRoute('listado_tutores');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar el tutor');
            }
        }
        return $this->render('tutores/eliminar.html.twig', [
            'tutor' => $tutor
        ]);
    }

}
