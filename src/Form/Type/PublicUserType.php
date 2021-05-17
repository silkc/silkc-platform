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

class PublicUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'form.label.firstname',
                'translation_domain' => 'login',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'form.label.lastname',
                'translation_domain' => 'login',
            ])
            ->add('username', TextType::class, [
                'label' => 'form.label.pseudo',
                'translation_domain' => 'login',
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.label.email',
                'translation_domain' => 'login',
            ])
            ->add('roles', HiddenType::class, [
                'label' => 'label.roles',
                'required'   => true,
                'data' => User::ROLE_USER
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
        ])
            ->add('save_and_create', SubmitType::class, [
                'label' => 'label.save_and_create_new',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => User::class,
                                   'require_password' => true,
                                   'csrf_protection' => false, // Possibilité de créer un compte en API
                               ]);
    }
}