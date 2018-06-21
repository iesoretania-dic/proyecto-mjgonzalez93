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

class AlumnoController extends Controller
{
    /**
     * @Route("/subida_alumno", name="subida alumno")
     */
    public function subirArchivoAction(Request $request) {

        if($request->getMethod() == "POST"){
            try {
                $carpeta = '/web/archivos/';
                $ruta = $this->get('kernel')->getRootDir();
                $ruta = substr($ruta, 0, -4);
                $destino = $ruta . $carpeta;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $_FILES['archivo']['name']);
                return $this->redirectToRoute('lectura alumno', ['archivo' => $_FILES['archivo']['name']]);
            }catch (\Exception $e){
                $this->addFlash('error', 'No se ha podido subir el archivo');
                return $this->redirectToRoute('subida alumno');
            }
        }
        return $this->render('alumnos/formulario.html.twig');
    }

    /**
     * @Route("/lectura_alumno/{archivo}", name="lectura alumno")
     */

    public function lecturaArchivoAction(Request $request, $archivo) {

        $em = $this->getDoctrine()->getManager();

        try {

            $conexion = mysqli_connect('localhost', 'root', 'oretania', 'atica_fct');
            if (!$conexion) {
                die('No se puede conectar: ' . mysqli_error($conexion));
            }

            $sentencia = "SELECT * FROM classroom";
            $resultado = $conexion->query($sentencia);
            $ID = [];

            while ($tupla = mysqli_fetch_assoc($resultado)) {
                $ID[] = $tupla;

            }

            $carpeta = '/web/archivos/';
            $ruta = $this->get('kernel')->getRootDir();
            $ruta = substr($ruta, 0, -4);
            $destino = $ruta . $carpeta;

            $csv = Reader::createFromPath($destino . $archivo)
                ->setHeaderOffset(0);

            foreach ($csv as $dato) {

                $usuarios = $this->getDoctrine()->getRepository('AppBundle:User')->listadoDNIAlumnos();

                $repetido = false;
                if($usuarios) {
                    foreach ($usuarios as $usuario) {
                        $dni = $dato['DNI/Pasaporte'];
                        if ($dni == $usuario['dni']) {
                            global $repetido;
                            $repetido = true;
                        }
                    }
                }
                if ($repetido == false) {
                    $user = new User();
                    $em->persist($user);

                    $contrasena = $dato['DNI/Pasaporte'];

                    $user->setLoginUsername($dato['DNI/Pasaporte']);
                    $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $contrasena));
                    $user->setReference($dato['DNI/Pasaporte']);
                    $user->setFirstName('' . $dato['Primer apellido'] . ' ' . $dato['Segundo apellido']);
                    $user->setLastName($dato['Nombre']);
                    $user->setEmail($dato['Correo Electrónico']);
                    $user->setGender('0');
                    $user->setGlobalAdministrator('0');
                    $user->setFinancialManager('0');
                    $user->setAllowExternalLogin('0');
                    $user->setExternalLogin('0');
                    $user->setEnabled('0');

                    foreach ($ID as $clase) {
                        if ($clase['name'] == $dato['Unidad']) {
                            $grupo = $this->getDoctrine()->getRepository('AppBundle:Group')->obtencionGrupo($clase['id']);
                            $user->setStudentGroup($grupo);
                        }
                    }
                    $em->flush();
                }
            }
            $this->addFlash('exito', 'Solicitud realizada correctamente ');
            return $this->redirectToRoute('listado_alumnos');
        }catch (\Exception $e){
            $this->addFlash('error', 'No se han podido guardar los cambios ');
            return $this->redirectToRoute('inicio');
        }
    }

    /**
     * @Route("/alumnos", name="listado_alumnos")
     */
    public function listadoAlumnosAction()
    {

        $alumnos = $this->getDoctrine()->getRepository('AppBundle:User')->listadoAlumnos();

        return $this->render('alumnos/listado.html.twig', [
            'alumnos' => $alumnos
        ]);
    }

    /**
     * @Route("/nuevo/alumno/", name="creacion_alumnos")
     * @Route("/editar/alumno/{id}", name="edicion_alumnos")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioAlumnosAction(Request $request, User $usuario = null){
        $em = $this->getDoctrine()->getManager();
        $nuevo = false;
        $modificar_perfil = true;
        $alumno = true;

        if (null === $usuario) {
            $usuario = new User();
            $nuevo = true;
            $modificar_perfil = false;
            $em->persist($usuario);
        }

        $form = $this->createForm(UserType::class, $usuario,['alumno' => $alumno, 'modificar_perfil' => $modificar_perfil ,'nuevo' => $nuevo]);
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
                return $this->redirectToRoute('listado_alumnos');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('alumnos/usuario.html.twig', [
            'usuario' => $usuario,
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/eliminar/alumno/{id}", name="eliminar_alumno")
     */
    public function eliminarAlumnoAction(Request $request, User $alumno){

        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            try {
                foreach($alumno->getStudentAgreements() as $acuerdo) {
                    $em->remove($acuerdo);
                };

                $em->remove($alumno);
                $em->flush();
                $this->addFlash('exito', 'Alumno eliminado con exito');
                return $this->redirectToRoute('listado_alumnos');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar el alumno');
            }
        }
        return $this->render('alumnos/eliminar.html.twig', [
            'alumno' => $alumno
        ]);
    }


}
