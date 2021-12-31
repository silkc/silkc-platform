<?php

namespace App\Form\Type;

use App\Entity\Occupation;
use App\Entity\Position;
use App\Entity\User;
use App\Form\DataTransformer\SkillsToJsonTransformer;
use App\Repository\OccupationRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{
    private $transformer;
    private $occupationRepository;
    private $userRepository;

    public function __construct(OccupationRepository $occupationRepository, UserRepository $userRepository, SkillsToJsonTransformer $transformer)
    {
        $this->transformer          = $transformer;
        $this->occupationRepository = $occupationRepository;
        $this->userRepository       = $userRepository;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Position::class, 'is_user' => false]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr'               => ['autofocus' => true],
                'translation_domain' => 'messages',
                'label'              => 'label.name',
            ])
            ->add('location', HiddenType::class, [
                'attr'               => ["class" => "input_user_address"],
                'translation_domain' => 'messages',
                'label'              => 'label.location',
                'required'           => false,
            ])
            ->add('longitude', HiddenType::class, [
                'attr'               => ["class" => "user_lng"],
                'required'           => false,
            ])
            ->add('latitude', HiddenType::class, [
                'attr'               => ["class" => "user_lat"],
                'required'           => false,
            ])
            ->add('description', TextareaType::class, [
                'attr'               => ['rows' => 8/*, 'class' => 'tinymce'*/],
                'translation_domain' => 'messages',
                'label'              => 'label.description',
                'required'           => false,
            ])
            ->add('salary', NumberType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.salary',
                'required'           => false,
                'attr' => ['placeholder' => 'placeholder.salary_price']
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'label.currency',
                'required' => true,
                'choices' => Position::getCurrencies(TRUE),
                'attr' => ['class' => 'custom-select']
            ])
            ->add('isVisible', CheckboxType::class, [
                'label'    => 'published_for_job_seekers',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.start_at',
                'required'           => false,
                'widget'             => 'single_text',
            ])
            ->add('endAt', DateTimeType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.end_at',
                'required'           => false,
                'widget'             => 'single_text',
            ])
            ->add('occupation', EntityType::class, [
                'attr'         => ["class" => "selectpicker", "data-live-search" => "true"],
                'class'        => Occupation::class,
                'choices'      => $this->occupationRepository->findAll(),
                'choice_label' => 'preferredLabel',
                'label'        => 'label.position_occupation',
                'multiple'     => false,
                'expanded'     => false,
                'required'     => false,
                'by_reference' => true,
                'placeholder'  => '',
                'choice_attr' => function($choice, $key, $value) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['data-description' => $choice->getDescription()];
                },
            ]);

        if (is_array($options) && array_key_exists('is_user', $options) && $options['is_user'] === true) {
            $builder->add('user', EntityType::class, [
                'attr'               => ["class" => "selectpicker"],
                'class'              => User::class,
                'choices'            => $this->userRepository->findByRole(User::ROLE_INSTITUTION),
                'choice_label'       => 'username',
                'multiple'           => false,
                'expanded'           => false,
                'required'           => false,
                'by_reference'       => true,
                'placeholder'        => '',
                'label'              => 'label.institution_name',
                'translation_domain' => 'messages',
            ]);
        }
        /*->add('trainingSkills', HiddenType::class, [
        'required' => false,
        ])*/
        $builder->add('save', SubmitType::class, [
            'translation_domain' => 'messages',
            'label'              => 'label.save',
        ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {});

        /*$builder->get('trainingSkills')
    ->addModelTransformer($this->transformer);*/
    }
}
