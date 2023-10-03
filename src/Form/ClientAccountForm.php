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
use App\Entity\ClientEntity;
use App\Entity\PurokEntity;



class ClientAccountForm extends AbstractType
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
            ->add('connectionType', ChoiceType::class, [
                'label' => 'Plan',
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'choices'  => [
                    'Plan 499' => '499',
                    'Plan 999' => '999',
                    'Plan 1099' => '1099',
                    'Plan 1299' => '1299',
                    'Plan 1499' => '1499',
                    'Plan 1599' => '1599',
                    'Plan 1699' => '1699',
                    'Plan 1799' => '1799',
                    'Plan 1999' => '1999',
                    'Plan 2499' => '2499',
                    'Plan 2999' => '2999'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],

                'choices'  => [
                    'Active' => 'Active',
                    'Disconnected' => 'Disconnected'
                ]
            ])
            ->add('description', TextType::class, array(
                'label' => 'Description',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control required'],
                'required' => true
            ))
            ->add('remarks', TextareaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false
            ))
            ->add('oldBalance', TextType::class, array(
                'label' => 'Remaining Balance Before System',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false
            ))
            ->add('client', HiddenType::class, array('data' => $options['clientId']))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {

                $form = $event->getForm();
                $data = $event->getData();
                 
            });

            $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, true, $options['clientId']));


    }

    public function getName()
    {
        return 'clientAccount';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientAccountEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'clientId'    => null 
        ));
    }
}