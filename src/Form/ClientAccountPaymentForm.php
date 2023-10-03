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
use Symfony\Component\Form\Extension\Core\Type\FileType;



use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Form\DataTransformer\DatetimeTransformer;
use App\Entity\ClientAccountEntity;
use App\Entity\PaymentTypeEntity;




class ClientAccountPaymentForm extends AbstractType
{
   
    private $manager;
    private $transactionNo;
    private $action;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->transactionNo = $options['transactionNo'];
        $this->action  = $options['action'];
        $builder
            ->add('action', HiddenType::class, array(
                'data' => $options['action'],
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
                       
            ->add('paymentDate', TextType::class, [
                'label' => 'Payment Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('refNo', TextType::class, [
                'label' => 'Reference No.',
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('amount', TextType::class, [
                'label' => 'Amount',
                'attr' => [ 'class' => 'form-control amount'],
                'required' => 'required',
            ])
            ->add('amount_tendered', TextType::class, [
                'label' => 'Tendered Amount',
                'attr' => [ 'class' => 'form-control amount'],
                'required' => 'required',
            ])
            ->add('amount_change', TextType::class, [
                'label' => 'Change',
                'attr' => [
                     'class' => 'form-control amount',
                     'readonly' => true
                ],
                'required' => 'required',
            ])
            ->add('file', FileType::class, array(
                'label' => 'Upload Payment Photo',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
                'mapped' => false 
            ))
            ->add('clientAccount', HiddenType::class, array('data' => $options['clientAccountId']))
            ->add('paymentType', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {


                $form = $event->getForm();
                $data = $event->getData();
                $paymentType = $data->getPaymentType();

                $form
                    ->add('payment_type_desc', TextType::class, array(
                        'label' => 'Payment Type',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $paymentType ? $paymentType->getDescription() : ''
                    ))

                    ->add('transactionNo', TextType::class, [
                        'label' => 'Transaction No.',
                        'attr' => [ 'class' => 'form-control', 'readonly' => true],
                        'required' => false,
                        'data' => $this->action == 'n' ? $this->transactionNo : $data->getTransactionNo()
                    ]);
                 
            });
            $builder->get('clientAccount')->addModelTransformer(new DataTransformer($this->manager, ClientAccountEntity::class, true, $options['clientAccountId']));
            $builder->get('paymentDate')->addModelTransformer(new DatetimeTransformer());
            $builder->get('paymentType')->addModelTransformer(new DataTransformer($this->manager, PaymentTypeEntity::class, false));




    }

    public function getName()
    {
        return 'clientAccountPayment';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientAccountPaymentEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'clientAccountId'    => null,
            'transactionNo' => ''
        ));
    }
}