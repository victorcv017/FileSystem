<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use \Symfony\Component\Form\Extension\Core\Type\SubmitType;
use \Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class , array(
                                                    'label'=>false,
                                                    'attr'=>array(
                                                                'class'=>'form-control',
                                                                'placeholder'=>'Nombre'
                                                        
                                                            )
                                                ))
                ->add('surname', TextType::class, array(
                                                    'label'=>false,
                                                    'attr'=>array(
                                                                'class'=>'form-control',
                                                                'placeholder'=>'Apellido'
                                                        
                                                            )
                                                ))
                ->add('email', EmailType::class, array(
                                                    'label'=>false,
                                                    'attr'=>array(
                                                                'class'=>'form-control',
                                                                'placeholder'=>'Correo'
                                                        
                                                            )
                                                ))
                ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'Las contraseñas deben coincidir.',
                   
                    'required' => true,
                    'first_options' => array('label' => false, 'attr'=>array(
                                                                'class'=>'form-control',
                                                                'placeholder'=>'Contraseña'
                                                        
                                                            )),
                    'second_options' => array('label' => false, 'attr'=>array(
                                                                'class'=>'form-control',
                                                                'placeholder'=>'Confirmar Contraseña'
                                                        
                                                            )),
                ))->add('registrar', SubmitType::class,  array(
                                                    'label'=>'Unirse',
                                                    'attr'=>array(
                                                                'class'=>'btn btn-lg btn-primary btn-block',
                                                                
                                                        
                                                            )
                                                ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }


}
