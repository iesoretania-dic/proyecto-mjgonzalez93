<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\User;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsuarioController extends Controller
{
    /**
     * @Route("/subida_usuario", name="subida usuario")
     */
    public function subirArchivoAction(Request $request) {

        if($request->getMethod() == "POST"){
            try {
                $carpeta = '/web/archivos/';
                $ruta = $this->get('kernel')->getRootDir();
                $ruta = substr($ruta, 0, -4);
                $destino = $ruta . $carpeta;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $_FILES['archivo']['name']);
                return $this->redirectToRoute('lectura usuario', ['archivo' => $_FILES['archivo']['name']]);
            }catch (\Exception $e){
                $this->addFlash('error', 'No se ha podido subir el archivo');
                return $this->redirectToRoute('subida archivo');
            }
        }
        return $this->render('usuarios/formulario.html.twig');
    }

    /**
     * @Route("/lectura_usuario/{archivo}", name="lectura usuario")
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

            $usuarios = $this->getDoctrine()->getRepository('AppBundle:User')->listadoUsuarios();

            foreach ($csv as $dato) {
                $repetido = false;
                foreach ($usuarios as $usuario) {
                    $dni = $dato['DNI/Pasaporte'];
                    if ($dni == $usuario['dni']) {
                        global $repetido;
                        $repetido = true;
                    }
                }
                if ($repetido == false) {
                    $user = new User();
                    $em->persist($user);

                    $contrasena = $dato['DNI/Pasaporte'];

                    $user->setLoginUsername($dato['DNI/Pasaporte']);
                    $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $contrasena));
                    $user->setFirstName('' . $dato['Primer apellido'] . ' ' . $dato['Segundo apellido']);
                    $user->setLastName($dato['Nombre']);
                    //$user->setEmail($dato['Correo Electrónico']);
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
            return $this->redirectToRoute('inicio');
        }catch (\Exception $e){
            $this->addFlash('error', 'No se han podido guardar los cambios ' .$e);
            return $this->redirectToRoute('inicio');
        }
    }

}
