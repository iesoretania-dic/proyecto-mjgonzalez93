<?php

namespace AppBundle\Form\Type;
use AppBundle\Entity\User;
use AppBundle\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('loginUsername', null, [
                'label' => 'Login',
                'required' => false,
                'disabled' => !$options['admin']
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => 'Email',
                'required' => false,
                'disabled' => !$options['admin']
            ])
            ->add('reference', null, [
                'label' => 'DNI',
                'required' => false
            ])
            ->add('lastName', null, [
                'label' => 'Nombre',
                'required' => true
            ])
            ->add('firstName', null, [
                'label' => 'Apellidos',
                'required' => true
            ])
            ->add('displayName', null, [
                'label' => 'Nombre mostrado',
                'required' => false])
            ->add('gender', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'Genero',
                'choices' => [
                    'Desconocido' => Person::GENDER_UNKNOWN,
                    'Hombre' => Person::GENDER_MALE,
                    'Mujer' => Person::GENDER_FEMALE
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ])
            ->add('enabled', null, [
                'label' => 'El usuario está activo',
                'required' => false
            ])
            ->add('financialManager', null, [
                'label' => 'Es responsable económico',
                'required' => false
            ])
            ->add('tutorizedGroups', null, [
                'label' => 'Es tutor de los grupos',
                'by_reference' => false,
                'required' => false,
                'disabled' => !$options['admin']
            ])
            ->add('studentGroup', null, [
                'label' => 'Es un estudiante del grupo',
                'placeholder' => 'Es un estudiante del grupo',
                'required' => false,
                'disabled' => !$options['admin']
            ]);

            if (true === $options['el_mismo']) {
                $builder
                ->add('antigua', PasswordType::class, [
                    'label' => 'Antigua contraseña',
                    'mapped' => false,
                    'constraints' => [
                        new UserPassword()
                    ]
                ]);
            }

            $builder
            ->add('nueva', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'required' => false,
                'first_options' => [
                    'label' => 'Nueva contraseña',
                ],
                'second_options' => [
                    'label' => 'Repita nueva contraseña',
                    'required' => false
                ]
            ]);

    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'nuevo' => false,
            'admin' => true,
            'el_mismo' => false
        ]);
    }
}