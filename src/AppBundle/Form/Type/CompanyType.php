<?php

namespace AppBundle\Form\Type;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class CompanyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder
                ->add('code', null,[
                    'label' => 'CIF',
                    'required' => true
                ])
                ->add('name', null, [
                    'label' => 'Nombre',
                    'required' => true
                ])
                ->add('manager', EntityType::class, [
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.financialManager = true');
                    },
                    'label' => 'Manager',
                    'attr' => array('class' => 'select2'),
                ])
                ->add('city', null, [
                    'label' => 'Ciudad',
                    'required' => true
                ])
                ->add('province', null, [
                    'label' => 'Provincia',
                    'required' => true
                ])
                ->add('zipCode', null, [
                    'label' => 'Codigo Postal',
                    'required' => true
                ])
                ->add('address', null, [
                    'label' => 'Direccion',
                    'required' => true
                ])
                ->add('zipCode', null, [
                    'label' => 'Codigo Postal',
                    'required' => true
                ])
                ->add('phoneNumber', null, [
                    'label' => 'Numero telefono',
                    'required' => false
                ])
                ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => 'Email',
                'required' => false
                ]);

        }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class
        ]);
    }
}