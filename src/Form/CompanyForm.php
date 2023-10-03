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


class CompanyForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('code', TextType::class, array(
                'label' => 'Code',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('description', TextType::class, array(
                'label' => 'Description',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
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
            ->add('email', TextType::class, array(
                'label' => 'Email',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))   
             ->add('logo', FileType::class, array(
                'label' => 'Upload Logo',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => array(
                    'class' => 'form-control ',
                    'accept' => "image/png, image/gif, image/jpeg"
                ),
                'required' => false,
                'mapped' => false
            ));

    }

    public function getName()
    {
        return 'company';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\CompanyEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'companyEntity_intention',
            'action'          => 'n'
        ));
    }
}