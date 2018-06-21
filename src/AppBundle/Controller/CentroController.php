<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use AppBundle\Entity\Workcenter;
use AppBundle\Form\Type\CompanyType;
use AppBundle\Form\Type\UserType;
use AppBundle\Form\Type\WorkcenterType;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CentroController extends Controller
{

    /**
     * @Route("/centros", name="listado_centros")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listadoCentrosAction()
    {

        $centros = $this->getDoctrine()->getRepository('AppBundle:Workcenter')->listadoCentros();

        return $this->render('centros/listado.html.twig', [
            'centros' => $centros
        ]);
    }

    /**
     * @Route("/nuevo/centro/", name="creacion_centros")
     * @Route("/editar/centro/{id}", name="edicion_centros")
     * @IsGranted("ROLE_ADMIN")
     */
    public function formularioEmpresaAction(Request $request, Workcenter $centro = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (null === $centro) {
            $centro = new Workcenter();
            $em->persist($centro);
        }

        $form = $this->createForm(WorkcenterType::class, $centro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ');
                return $this->redirectToRoute('listado_centros');
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }

        }

        return $this->render('centros/centro.html.twig', [
            'centro' => $centro,
            'formulario' => $form->createView()
        ]);
    }
}
