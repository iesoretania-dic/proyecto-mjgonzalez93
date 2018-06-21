<?php

namespace AppBundle\Form\Type;
use AppBundle\Entity\Agreement;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use AppBundle\Entity\Workcenter;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AgreementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder
                ->add('student', EntityType::class, [
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->where('a.studentGroup != false');
                    },
                    'label' => 'Alumno',
                    'attr' => array('class' => 'select2'),
                    ])
                ->add('workcenter', EntityType::class, [
                    'class' => Workcenter::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('w');
                    },
                    'label' => 'Centro de trabajo',
                    'attr' => array('class' => 'select2'),
                ])
                ->add('workTutor', EntityType::class, [
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.financialManager = true');
                    },
                    'label' => 'Tutor laboral',
                    'attr' => array('class' => 'select2'),
                ])
                ->add('educationalTutor', EntityType::class, [
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->addSelect('t')
                            ->join('u.tutorizedGroups', 't');
                    },
                    'label' => 'Tutor docente',
                    'attr' => array('class' => 'select2'),
                ])
                ->add('signDate', DateType::class , [
                    'widget' => 'single_text',
                    'label' => 'Fecha de firma',
                    'required' => false
                ])
                ->add('fromDate', DateType::class , [
                    'widget' => 'single_text',
                    'label' => 'Fecha inicio',
                    'required' => true
                ])
                ->add('toDate', DateType::class , [
                    'widget' => 'single_text',
                    'label' => 'Fecha final',
                    'required' => true
                ])
                ->add('quarter', null, [
                    'label' => 'Trimestre',
                    'required' => false
                ]);

        }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agreement::class
        ]);
    }
}