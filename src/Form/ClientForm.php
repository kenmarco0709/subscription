<?php

namespace App\Form;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Entity\BranchEntity;


class ClientForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            ->add('action', HiddenType::class, array(
                'data' => $options['action'],
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('first_name', TextType::class, array(
                'label' => 'First Name',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true,
            ))
            ->add('last_name', TextType::class, array(
                'label' => 'Last Name',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true
            ))
            ->add('address', TextType::class, array(
                'label' => 'Address',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('contact_no', TextType::class, array(
                'label' => 'Contact No.',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))

            ->add('branch', HiddenType::class, array('data' => $options['branchId']));
            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));

    }

    public function getName()
    {
        return 'client';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'branchId'    => null 
        ));
    }
}