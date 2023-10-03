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

class SmsForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $smsTypes = array();

        foreach($options['smsTypes'] as $row) {
                $smsTypes[$row] = $row;
        }

        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('sms_type', ChoiceType::class, array(
                'label' => 'Type',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'choices' => $smsTypes,
                'attr' => [ 'class' => 'form-control', 'required' => 'required']

            ))

            ->add('message', TextareaType::class, array(
                'label' => 'Type',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'attr' => [ 'class' => 'form-control', 'required' => 'required', 'rows' => 10]

            ))
            ->add('company', HiddenType::class, array('data' => $options['companyId']));
           
           $builder->get('company')->addModelTransformer(new DataTransformer($this->manager, CompanyEntity::class, true, $options['companyId']));

    }

    public function getName()
    {
        return 'sms';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\SmsEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'smsEntity_intention',
            'action'          => 'n',
            'smsTypes'    => array(),
            'companyId'    => null 
        ));
    }
}