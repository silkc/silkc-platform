<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'login.form.label.current_password',
                'translation_domain' => 'messages',
                'required'   => true,
            ])
            ->add('password', RepeatedType::class, [
                'type'               => PasswordType::class,
                'translation_domain' => 'messages',
                'required'           => (array_key_exists('require_password', $options) && $options['require_password'] === true) ? true : false,
                'first_options'      => ['label' => 'login.form.label.password', "always_empty" => true],
                'second_options'     => ['label' => 'login.form.label.confirm_password', 'always_empty' => true],
                'invalid_message'    => 'The passwords entered are not identical.',
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'label.save',
            'translation_domain' => 'messages',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => User::class
       ]);
    }
}