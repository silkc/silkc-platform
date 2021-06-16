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
                'label' => 'login.form.label.firstname',
                'translation_domain' => 'trad',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'login.form.label.lastname',
                'translation_domain' => 'trad',
            ])
            ->add('username', TextType::class, [
                'label' => 'login.form.label.pseudo',
                'translation_domain' => 'trad',
            ])
            ->add('email', EmailType::class, [
                'label' => 'login.form.label.email',
                'translation_domain' => 'trad',
            ])
            ->add('roles', HiddenType::class, [
                'label' => 'login.form.label.roles',
                'translation_domain' => 'trad',
                'required'   => true,
                'data' => User::ROLE_USER
            ]);
        if (array_key_exists('require_password', $options) && $options['require_password'] === true) {
            $builder->add('password', RepeatedType::class, [
                'type'               => PasswordType::class,
                'translation_domain' => 'trad',
                'required'           => (array_key_exists('require_password', $options) && $options['require_password'] === true) ? true : false,
                'first_options'      => ['label' => 'login.form.label.password', "always_empty" => true],
                'second_options'     => ['label' => 'login.form.label.confirm_password', 'always_empty' => true],
                'invalid_message'    => 'Les mots de passe saisis ne sont pas identiques.',
            ]);
        }
        $builder->add('save', SubmitType::class, [
            'translation_domain' => 'trad',
            'label' => 'label.save'
        ])
            ->add('save_and_create', SubmitType::class, [
                'translation_domain' => 'trad',
                'label' => 'login.form.button.save_and_create'
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