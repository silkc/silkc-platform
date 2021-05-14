<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if (array_key_exists('is_personal', $options) && $options['is_personal'] === true) {
            $builder
                ->add('username', TextType::class, [
                    'label' => 'form.label.pseudo',
                    'translation_domain' => 'login',
                    'required'   => true,
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'form.label.firstname',
                    'translation_domain' => 'login',
                    'required'   => false,
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'form.label.lastname',
                    'translation_domain' => 'login',
                    'required'   => false,
                ]);
        } else {
            $builder
                ->add('username', TextType::class, [
                    'label' => 'form.label.institution_name',
                    'translation_domain' => 'login',
                    'required'   => true,
                ])
                ->add('homepage', TextType::class, [
                    'label'              => 'form.label.homepage',
                    'translation_domain' => 'login',
                    'required'           => false,
                ])
                ->add('address', TextType::class, [
                    'label'              => 'form.label.address',
                    'translation_domain' => 'login',
                    'required'           => false,
                ]);
        }
        /*
            $builder->add('roles', ChoiceType::class, [
                'label'    => 'label.roles',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'choices'  => User::getRolesList(),
            ]);
        */

        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.label.email',
                'translation_domain' => 'login',
            ]);

            if (array_key_exists('require_password', $options) && $options['require_password'] === true) {
                $builder->add('password', RepeatedType::class, [
                    'type'               => PasswordType::class,
                    'translation_domain' => 'login',
                    'required'           => (array_key_exists('require_password', $options) && $options['require_password'] === true) ? true : false,
                    'first_options'      => ['label' => 'form.label.password', "always_empty" => true],
                    'second_options'     => ['label' => 'form.label.confirm_password', 'always_empty' => true],
                    'invalid_message'    => 'Les mots de passe saisis ne sont pas identiques.',
                ]);
            }
            $builder->add('save', SubmitType::class, [
                'label' => 'form.button.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => false,
            'is_personal' => false,
            'csrf_protection' => false, // Possibilité de créer un compte en API
        ]);
    }
}