<?php

namespace App\Form\Type;

use App\Entity\Occupation;
use App\Entity\Training;
use App\Entity\User;
use App\Form\DataTransformer\SkillsToJsonTransformer;
use App\Repository\OccupationRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
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
use Symfony\Component\Intl\Locale;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingType extends AbstractType
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
        $resolver->setDefaults(['data_class' => Training::class, 'is_user' => false, 'can_validate' => false, 'locale' => null]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = (is_array($options) && array_key_exists('locale', $options) && !empty($options['locale'])) ?
            $options['locale'] :
            null;

        $builder
            ->add('name', TextType::class, [
                'attr'               => ['autofocus' => true],
                'translation_domain' => 'messages',
                'label'              => 'label.name',
            ])
            ->add('location', HiddenType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.location',
                'required'           => false,
            ])
            ->add('longitude', HiddenType::class, [
                'required'           => false,
            ])
            ->add('latitude', HiddenType::class, [
                'required'           => false,
            ])
            ->add('durationValue', IntegerType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.duration_value',
                'required'           => false,
            ])
            ->add('durationUnity', ChoiceType::class, [
                'translation_domain' => 'messages',
                'required' => true,
                'choices' => Training::getUnities(TRUE),
                'attr' => ['class' => 'custom-select']
            ])
            ->add('durationDetails', TextType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.duration_details',
                'required'           => false,
                'attr' => ['placeholder' => 'placeholder.duration_details']
            ])
            ->add('description', TextareaType::class, [
                'attr'               => ['rows' => 8/*, 'class' => 'tinymce'*/],
                'translation_domain' => 'messages',
                'label'              => 'label.description',
                'required'           => false,
            ])
            ->add('price', NumberType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.price',
                'required'           => false,
                'attr' => ['placeholder' => 'placeholder.training_price']
            ])
            ->add('isFree', CheckboxType::class, [
                'translation_domain' => 'messages',
                /*'row_attr' => ['class' => 'switch-custom'],
                'attr' => ['class' => 'custom-control-input'],*/
                'label'              => 'label.is_free',
                'label_attr' => [
                    'class' => 'switch-custom',
                ],
                'required' => false
            ])
            ->add('isCertified', ChoiceType::class, [
                'translation_domain' => 'messages',
                'choices'  => array(
                    'yes' => 1,
                    'no' => 0,
                ),
                'label'              => false,
                'expanded' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'label.currency',
                'required' => true,
                'choices' => Training::getCurrencies(TRUE),
                'attr' => ['class' => 'custom-select']
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'label.language',
                'required' => true,
                'choices' => Training::getLanguages(TRUE),
                'attr' => ['class' => 'custom-select']
            ])
            ->add('url', TextType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.training_info_url',
                'required'           => false,
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
            ->add('isOnline', ChoiceType::class, [
                'translation_domain' => 'messages',
                'choices'  => array(
                    'yes' => 1,
                    'no' => 0,
                ),
                'label'              => false,
                'expanded' => true,
            ])
            ->add('isOnlineMonitored', ChoiceType::class, [
                'translation_domain' => 'messages',
                'choices'  => array(
                    'yes' => 1,
                    'no' => 0,
                ),
                'label'              => false,
                'expanded' => true,
            ])
            ->add('isPresential', ChoiceType::class, [
                'translation_domain' => 'messages',
                'choices'  => array(
                    'yes' => 1,
                    'no' => 0,
                ),
                'label'              => false,
                'expanded' => true,
            ])
            ->add('occupation', EntityType::class, [
                'attr'         => ["class" => "selectpicker", "data-live-search" => "true"],
                'class'        => Occupation::class,
                'choices'      => $this->occupationRepository->findAllNotNativeByLocale('fr'),
                'choice_label' => 'preferredLabel',
                'choice_label' => function ($occupationsTranslations) use ($locale) {
                    return $occupationsTranslations->getPreferredLabel($locale);
                },
                'multiple'     => false,
                'expanded'     => false,
                'required'     => false,
                'by_reference' => true,
                'translation_domain' => 'messages',
                'placeholder'  => 'no_item_selected',
                'choice_attr' => function($choice, $key, $value) use($locale) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['data-description' => $choice->getDescription($locale)];
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
        $builder
            ->add('save', SubmitType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.save',
            ]);

        if (is_array($options) && array_key_exists('can_validate', $options) && $options['can_validate'] === true) {
            $builder->add('save_and_validate', SubmitType::class, [
                'translation_domain' => 'messages',
                'label'              => 'label.save_and_validate',
            ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {});

        /*$builder->get('trainingSkills')
    ->addModelTransformer($this->transformer);*/
    }
}
