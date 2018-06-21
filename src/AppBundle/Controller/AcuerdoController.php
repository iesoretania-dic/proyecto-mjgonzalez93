<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\Agreement;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use AppBundle\Entity\Workcenter;
use AppBundle\Form\Type\AgreementType;
use AppBundle\Form\Type\CompanyType;
use AppBundle\Form\Type\UserType;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AcuerdoController extends Controller
{
    /**
     * @Route("/subida_acuerdo", name="subida acuerdo")
     */
    public function subirArchivoAcuerdoAction(Request $request) {

        if($request->getMethod() == "POST"){
            try {
                $carpeta = '/web/archivos/';
                $ruta = $this->get('kernel')->getRootDir();
                $ruta = substr($ruta, 0, -4);
                $destino = $ruta . $carpeta;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $_FILES['archivo']['name']);
                return $this->redirectToRoute('lectura acuerdo', ['archivo' => $_FILES['archivo']['name']]);
            }catch (\Exception $e){
                $this->addFlash('error', 'No se ha podido subir el archivo');
                return $this->redirectToRoute('subida acuerdo');
            }
        }
        return $this->render('acuerdos/formulario.html.twig');
    }

    /**
     * @Route("/lectura_acuerdo/{archivo}", name="lectura acuerdo")
     */

    public function lecturaArchivoAcuerdoAction(Request $request, $archivo) {

        $em = $this->getDoctrine()->getManager();

        try {

            $carpeta = '/web/archivos/';
            $ruta = $this->get('kernel')->getRootDir();
            $ruta = substr($ruta, 0, -4);
            $destino = $ruta . $carpeta;

            $csv = Reader::createFromPath($destino . $archivo)
                ->setHeaderOffset(0);

            foreach ($csv as $dato) {

                $nombreCompleto = $dato['Alumno/a'];
                $nombreCompleto = explode(",", $nombreCompleto);

                $apellidos = trim($nombreCompleto[0]);
                $nombre = trim($nombreCompleto[1]);

                $alumno = $this->getDoctrine()->getRepository('AppBundle:User')->obtenerUsuario($apellidos, $nombre);

                $dni = $dato['Tutor/a laboral'];
                $tutorLaboral = $this->getDoctrine()->getRepository('AppBundle:User')->obtenerUsuarioDNI($dni);

                $dni = $dato['Tutor/a docente'];
                $tutorDocente = $this->getDoctrine()->getRepository('AppBundle:User')->obtenerUsuarioDNI($dni);

                $acuerdos = $this->getDoctrine()->getRepository('AppBundle:Agreement')->listadoAcuerdos();

                $cif = $dato['Código del Centro/ CIF/NIF de la Empresa'];
                $empresa = $this->getDoctrine()->getRepository('AppBundle:Company')->obtenerEmpresa($cif);
                $centro = $this->getDoctrine()->getRepository('AppBundle:Workcenter')->obtenerCentros($empresa);

                if(count($alumno)==1) {

                    $acuerdo = new Agreement();

                    $em->persist($acuerdo);

                    $acuerdo->setStudent($alumno[0]);
                    $acuerdo->setEducationalTutor($tutorLaboral);
                    $acuerdo->setWorkTutor($tutorDocente);
                    $acuerdo->setWorkcenter($centro);
                    $acuerdo->setFromDate(new \DateTime($dato['Fecha de inicio de las prácticas']));
                    $acuerdo->setToDate(new \DateTime($dato['Fecha de finalización de las prácticas']));

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

    /**
     * @Route("/acuerdos", name="listado_acuerdos")
     */
    public function listadoEmpresasAction()
    {

        $acuerdos = $this->getDoctrine()->getRepository('AppBundle:Agreement')->listadoAcuerdos();

        return $this->render('acuerdos/listado.html.twig', [
            'acuerdos' => $acuerdos
        ]);
    }

    /**
     * @Route("/nuevo/acuerdo/", name="creacion_acuerdo")
     * @Route("/editar/acuerdo/{id}", name="edicion_acuerdo")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioAcuerdoAction(Request $request, Agreement $acuerdo = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (null === $acuerdo) {
            $acuerdo = new Agreement();
            $em->persist($acuerdo);
        }

        $form = $this->createForm(AgreementType::class, $acuerdo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ');
                return $this->redirectToRoute('listado_acuerdos');
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('acuerdos/acuerdo.html.twig', [
            'acuerdo' => $acuerdo,
            'formulario' => $form->createView()
        ]);
    }
}
