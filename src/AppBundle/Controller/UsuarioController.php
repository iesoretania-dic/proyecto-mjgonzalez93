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
     * @Route("/subida_formulario", name="subida archivo")
     */
    public function subirArchivoAction(Request $request) {

        $usuario = $this->getUser();

        if($request->getMethod() == "POST"){
            $carpeta = '/web/archivos/';
            $ruta = $this->get('kernel')->getRootDir();
            $ruta = substr($ruta, 0, -4);
            $destino = $ruta.$carpeta;
            move_uploaded_file($_FILES['archivo']['tmp_name'], $destino.$_FILES['archivo']['name']);
            return $this->redirectToRoute('lectura archivo', ['archivo' => $_FILES['archivo']['name']]);

        }
        return $this->render('usuarios/formulario.html.twig');
    }

    /**
     * @Route("/lectura_archivos/{archivo}", name="lectura archivo")
     */

    public function lecturaArchivoAction(Request $request, $archivo) {

        $em = $this->getDoctrine()->getManager();

        $conexion = mysqli_connect('localhost','root','oretania','atica_fct');
        if (!$conexion) {
            die('No se puede conectar: ' . mysqli_error($conexion));
        }

        $sentencia="SELECT name FROM classroom";
        $resultado = mysqli_query($conexion,$sentencia);
        $ID = [];

        while($tupla = mysqli_fetch_assoc($resultado)) {
            $ID[] = $tupla;
            dump($tupla);
        }

        $carpeta = '/web/archivos/';
        $ruta = $this->get('kernel')->getRootDir();
        $ruta = substr($ruta, 0, -4);
        $destino = $ruta.$carpeta;

        $csv =  Reader::createFromPath($destino.$archivo)
            ->setHeaderOffset(0)
        ;

        $usuarios = $this->getDoctrine()->getRepository('AppBundle:User')->listadoUsuarios();

        foreach ($csv as $dato) {
            $repetido = false;
            foreach ($usuarios as $usuario){
                $dni = $dato['DNI/Pasaporte'];
                if($dni == $usuario){
                    $repetido = true;
                }
            }

            if($repetido == false){
                $user = new User();
                $em->persist($user);

                $contrasena = $dato['DNI/Pasaporte'];

                $user->setLoginUsername($dato['DNI/Pasaporte']);
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $contrasena));
                $user->setFirstName(''.$dato['Primer apellido'].' '.$dato['Segundo apellido']);
                $user->setFirstName($dato['Nombre']);
                $user->setEmail($dato['Correo Electrónico']);
                $user->setGender('0');
                $user->setGlobalAdministrator('0');
                $user->setFinancialManager('0');
                $user->setAllowExternalLogin('0');
                $user->setExternalLogin('0');
                $user->setEnabled('0');

                foreach ($ID as $clase) {
                    if($clase['name'] == $dato['Unidad']){
                        $idClase = $clase['id'];
                        $user->setStudentGroup($idClase);
                    }
                }

            }
        }

        exit();

        return $this->render('default/index.html.twig');
    }

}
