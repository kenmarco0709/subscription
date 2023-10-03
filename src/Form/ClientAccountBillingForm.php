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
use App\Form\DataTransformer\DatetimeTransformer;
use App\Entity\ClientAccountEntity;



class ClientAccountBillingForm extends AbstractType
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
            
            ->add('billingDate', TextType::class, [
                'label' => 'Billing Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('dueDate', TextType::class, [
                'label' => 'Due Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('clientAccount', HiddenType::class, array('data' => $options['clientAccountId']));

            $builder->get('clientAccount')->addModelTransformer(new DataTransformer($this->manager, ClientAccountEntity::class, true, $options['clientAccountId']));
            $builder->get('billingDate')->addModelTransformer(new DatetimeTransformer());
            $builder->get('dueDate')->addModelTransformer(new DatetimeTransformer());



    }

    public function getName()
    {
        return 'clientAccountBilling';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientAccountBillingEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'clientAccountId'    => null
        ));
    }
}