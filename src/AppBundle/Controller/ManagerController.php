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

class ManagerController extends Controller
{
    /**
     * @Route("/subida_manager", name="subida manager")
     */
    public function subirArchivoManagerAction(Request $request) {

        if($request->getMethod() == "POST"){
            try {
                $carpeta = '/web/archivos/';
                $ruta = $this->get('kernel')->getRootDir();
                $ruta = substr($ruta, 0, -4);
                $destino = $ruta . $carpeta;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $_FILES['archivo']['name']);
                return $this->redirectToRoute('lectura manager', ['archivo' => $_FILES['archivo']['name']]);
            }catch (\Exception $e){
                $this->addFlash('error', 'No se ha podido subir el archivo');
                return $this->redirectToRoute('subida manager');
            }
        }
        return $this->render('managers/formulario.html.twig');
    }

    /**
     * @Route("/lectura_manager/{archivo}", name="lectura manager")
     */

    public function lecturaArchivoManagerAction(Request $request, $archivo) {

        $em = $this->getDoctrine()->getManager();

        try {

            $carpeta = '/web/archivos/';
            $ruta = $this->get('kernel')->getRootDir();
            $ruta = substr($ruta, 0, -4);
            $destino = $ruta . $carpeta;

            $csv = Reader::createFromPath($destino . $archivo)
                ->setHeaderOffset(0);



            foreach ($csv as $dato) {

                $usuarios = $this->getDoctrine()->getRepository('AppBundle:User')->listadoDNIManagers();

                $repetido = false;
                if($usuarios) {
                    foreach ($usuarios as $usuario) {
                        $dni = $dato['DNI'];
                        if ($dni == $usuario['dni']) {
                            global $repetido;
                            $repetido = true;
                        }
                    }
                }

                if ($repetido == false) {
                    $user = new User();
                    $em->persist($user);

                    $nombre = explode( ',', $dato['Apellidos y Nombre']);

                    $contrasena = $dato['DNI'];

                    $user->setLoginUsername($dato['DNI']);
                    $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $contrasena));
                    $user->setReference($dato['DNI']);

                    $user->setFirstName($nombre[0]);
                    $user->setLastName(trim($nombre[1]));
                    $user->setGender('0');
                    $user->setGlobalAdministrator('0');
                    $user->setFinancialManager('1');
                    $user->setAllowExternalLogin('0');
                    $user->setExternalLogin('0');
                    $user->setEnabled('0');

                    $em->flush();
                }
            }
            $this->addFlash('exito', 'Solicitud realizada correctamente ');
            return $this->redirectToRoute('listado_managers');
        }catch (\Exception $e){
            $this->addFlash('error', 'No se han podido guardar los cambios ' .$e);
            return $this->redirectToRoute('inicio');
        }
    }

    /**
     * @Route("/managers", name="listado_managers")
     */
    public function listadoManagersAction()
    {

        $managers = $this->getDoctrine()->getRepository('AppBundle:User')->listadoManagers();

        return $this->render('managers/listado.html.twig', [
            'managers' => $managers
        ]);
    }

    /**
     * @Route("/nuevo/manager/", name="creacion_manager")
     * @Route("/editar/manager/{id}", name="edicion_managers")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioManagersAction(Request $request, User $usuario = null){
        $em = $this->getDoctrine()->getManager();
        $nuevo = false;
        $modificar_perfil = true;
        $manager = true;

        if (null === $usuario) {
            $usuario = new User();
            $nuevo = true;
            $modificar_perfil = false;
            $em->persist($usuario);
        }

        $form = $this->createForm(UserType::class, $usuario,['manager' => $manager, 'modificar_perfil' => $modificar_perfil ,'nuevo' => $nuevo]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                if($nuevo == true){
                    $dni = $form->get('reference')->getData();
                    $usuario->setLoginUsername($dni);
                    $usuario->setGlobalAdministrator(false);
                    $usuario->setAllowExternalLogin(false);
                    $usuario->setFinancialManager(true);
                    $usuario->setEnabled(false);
                    $clave = $this->get('security.password_encoder')->encodePassword($usuario, $dni);
                    $usuario->setPassword($clave);

                }
                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ' );
                return $this->redirectToRoute('listado_managers');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('managers/usuario.html.twig', [
            'usuario' => $usuario,
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/eliminar/manager/{id}", name="eliminar_manager")
     */
    public function eliminarManagerAction(Request $request, User $manager){

        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            try {

                $acuerdos = $this->getDoctrine()->getRepository('AppBundle:Agreement')->listadoAcuerdosManager($manager);

                foreach($acuerdos as $acuerdo) {
                    $em->remove($acuerdo);
                };

                $em->remove($manager);
                $em->flush();
                $this->addFlash('exito', 'Manager eliminado con exito');
                return $this->redirectToRoute('listado_managers');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar el manager ' .$e);
            }
        }
        return $this->render('managers/eliminar.html.twig', [
            'manager' => $manager
        ]);
    }


}
