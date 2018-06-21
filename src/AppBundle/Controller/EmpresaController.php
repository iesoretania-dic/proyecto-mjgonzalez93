<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use AppBundle\Entity\Workcenter;
use AppBundle\Form\Type\CompanyType;
use AppBundle\Form\Type\UserType;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class EmpresaController extends Controller
{
    /**
     * @Route("/subida_empresa", name="subida empresa")
     */
    public function subirArchivoEmpresaAction(Request $request) {

        if($request->getMethod() == "POST"){
            try {
                $carpeta = '/web/archivos/';
                $ruta = $this->get('kernel')->getRootDir();
                $ruta = substr($ruta, 0, -4);
                $destino = $ruta . $carpeta;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $_FILES['archivo']['name']);
                return $this->redirectToRoute('lectura empresa', ['archivo' => $_FILES['archivo']['name']]);
            }catch (\Exception $e){
                $this->addFlash('error', 'No se ha podido subir el archivo');
                return $this->redirectToRoute('subida empresa');
            }
        }
        return $this->render('empresas/formulario.html.twig');
    }

    /**
     * @Route("/lectura_empresa/{archivo}", name="lectura empresa")
     */

    public function lecturaArchivoEmpresaAction(Request $request, $archivo) {

        $em = $this->getDoctrine()->getManager();

        try {

            $carpeta = '/web/archivos/';
            $ruta = $this->get('kernel')->getRootDir();
            $ruta = substr($ruta, 0, -4);
            $destino = $ruta . $carpeta;

            $csv = Reader::createFromPath($destino . $archivo)
                ->setHeaderOffset(0);

            foreach ($csv as $dato) {

                $empresas = $this->getDoctrine()->getRepository('AppBundle:Company')->listadoEmpresas();
                $admin = $this->getDoctrine()->getRepository('AppBundle:User')->obtenerAdmin();

                $repetido = false;
                if($empresas) {
                    foreach ($empresas as $empresa) {
                        $cif = $dato['C.I.F.'];
                        if ($cif == $empresa->getCode()) {
                            global $repetido;
                            $repetido = true;
                        }
                    }
                }

                if ($repetido == false) {
                    $company = new Company();
                    $workcenter = new Workcenter();

                    $em->persist($company);
                    $em->persist($workcenter);

                    $company->setCode($dato['C.I.F.']);
                    $company->setName($dato['Nombre de la Empresa']);
                    $workcenter->setName($dato['Nombre de la Empresa']);
                    $company->setAddress($dato['Domicilio']);
                    $workcenter->setAddress($dato['Domicilio']);
                    $company->setCity("Linares");
                    $workcenter->setCity("Linares");
                    $company->setProvince("Jaen");
                    $workcenter->setProvince("Jaen");
                    $company->setZipCode("23700");
                    $workcenter->setZipCode("23700");
                    $company->setPhoneNumber($dato['Telefono']);
                    $workcenter->setPhoneNumber($dato['Telefono']);
                    $company->setEmail($dato['Email']);
                    $workcenter->setEmail($dato['Email']);

                    $company->setManager($admin);

                    $workcenter->setCompany($company);
                    $workcenter->setManager($admin);

                    $em->flush();
                }
            }

            $this->addFlash('exito', 'Solicitud realizada correctamente ');
            return $this->redirectToRoute('listado_empresas');
        }catch (\Exception $e){
            $this->addFlash('error', 'No se han podido guardar los cambios ');
            return $this->redirectToRoute('listado_empresas');
        }
    }

    /**
     * @Route("/empresas", name="listado_empresas")
     */
    public function listadoEmpresasAction()
    {

        $empresas = $this->getDoctrine()->getRepository('AppBundle:Company')->listadoEmpresas();

        return $this->render('empresas/listado.html.twig', [
            'empresas' => $empresas
        ]);
    }

    /**
     * @Route("/nueva/empresa/", name="creacion_empresas")
     * @Route("/editar/empresa/{id}", name="edicion_empresa")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioEmpresaAction(Request $request, Company $empresa = null)
    {
        $em = $this->getDoctrine()->getManager();
        $nuevo = false;

        if (null === $empresa) {
            $empresa = new Company();
            $nuevo = true;
            $em->persist($empresa);
        }

        $form = $this->createForm(CompanyType::class, $empresa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($nuevo == true) {

                    $workcenter = new Workcenter();
                    $em->persist($workcenter);

                    $manager = $form->get('manager')->getData();
                    $workcenter->setCompany($empresa);
                    $workcenter->setManager($manager);
                    $workcenter->setName($form->get('name')->getData());
                    $workcenter->setCity($form->get('city')->getData());
                    $workcenter->setProvince($form->get('province')->getData());
                    $workcenter->setZipCode($form->get('zipCode')->getData());
                    $workcenter->setAddress($form->get('address')->getData());
                    $numero = $form->get('phoneNumber')->getData();
                    $email = $form->get('email')->getData();
                    if($numero != null){
                        $workcenter->setPhoneNumber($numero);
                    }
                    if($email != null){
                        $workcenter->setEmail($email);
                    }

                }
                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ');
                return $this->redirectToRoute('listado_empresas');
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('empresas/empresa.html.twig', [
            'empresa' => $empresa,
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/eliminar/empresa/{id}", name="eliminar_empresa")
     */
    public function eliminarEmpresaAction(Request $request, Company $empresa){

        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            try {
                $workcenter = $this->getDoctrine()->getRepository('AppBundle:Workcenter')->obtenerCentros($empresa);

                $acuerdos = $this->getDoctrine()->getRepository('AppBundle:Agreement')->listadoAcuerdosEmpresa($workcenter);

                foreach($acuerdos as $acuerdo) {
                    $em->remove($acuerdo);
                };

                $em->remove($workcenter);
                $em->remove($empresa);
                $em->flush();
                $this->addFlash('exito', 'Empresa eliminado con exito');
                return $this->redirectToRoute('listado_empresas');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar la empresa');
            }
        }
        return $this->render('empresas/eliminar.html.twig', [
            'empresa' => $empresa
        ]);
    }

}
