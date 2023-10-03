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
use App\Entity\CompanyEntity;

use App\Entity\BranchEntity;


class UserForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $userTypes = array();

        foreach($options['userTypes'] as $row) {
            if($row !== 'Super Admin')
                $userTypes[$row] = $row;
        }

        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('username', TextType::class, array(
                'label' => 'Username',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'required' => true,
                'attr' => [ 'class' => 'form-control', 'required' => 'required']
            ))
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ],
                'attr' => [ 'class' => 'form-control', 'required' => 'required']           
             ])
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'required' => $options['action'] === 'n' ? true : false,
                'first_options' => array(
                    'label' => 'Password',
                    'label_attr' => array(
                        'class' => $options['action'] === 'n' ? 'required middle' : 'middle'
                    ),
                    'attr' => [ 'class' => 'form-control', 'required' =>  $options['action'] != 'u' ?  true : false ],
                    'empty_data' => ''  
                ),
                'second_options' => array(
                    'label' => 'Confirm Password',
                    'label_attr' => array(
                        'class' => $options['action'] === 'n' ? 'required middle' : 'middle'
                    ),
                    'attr' => [ 'class' => 'form-control']
                ),
                'label' => '',
                'empty_data' => ''

            ))
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
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('type', ChoiceType::class, array(
                'label' => 'Type',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'choices' => $userTypes,
                'attr' => [ 'class' => 'form-control', 'required' => 'required']

            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
            })
            ->add('branch', HiddenType::class)
            ->add('company', HiddenType::class, array('data' => $options['companyId']))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $branch = $data->getBranch();
                $form
       
                    ->add('branch_desc', TextType::class, array(
                        'label' => 'Branch',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $branch ? $branch->getDescription() : ''
                    ));
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false));
           $builder->get('company')->addModelTransformer(new DataTransformer($this->manager, CompanyEntity::class, true, $options['companyId']));

    }

    public function getName()
    {
        return 'user';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\UserEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'userEntity_intention',
            'action'          => 'n',
            'userTypes'    => array(),
            'companyId'    => null 
        ));
    }
}