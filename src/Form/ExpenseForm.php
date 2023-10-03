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
use App\Entity\ExpenseEntity;
use App\Entity\BranchEntity;

use App\Entity\ExpenseTypeEntity;





class ExpenseForm extends AbstractType
{
   
    private $manager;
    private $transactionNo;
    private $action;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

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
                       
            ->add('expenseDate', TextType::class, [
                'label' => 'Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('amount', TextType::class, [
                'label' => 'Amount',
                'attr' => [ 'class' => 'form-control amount'],
                'required' => 'required',
            ])
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->add('expenseType', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {


                $form = $event->getForm();
                $data = $event->getData();
                $expenseType = $data->getExpenseType();

                $form
                    ->add('expense_type_desc', TextType::class, array(
                        'label' => 'Expense Type',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $expenseType ? $expenseType->getDescription() : ''
                    ));
                 
            });

            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));
            $builder->get('expenseDate')->addModelTransformer(new DatetimeTransformer());
            $builder->get('expenseType')->addModelTransformer(new DataTransformer($this->manager, ExpenseTypeEntity::class, false));




    }

    public function getName()
    {
        return 'expense';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ExpenseEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'expenseEntity_intention',
            'action'          => 'n',
            'branchId'    => null
        ));
    }
}