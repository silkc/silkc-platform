<?php

namespace App\Form\Type;

use App\Entity\Training;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\SkillsToJsonTransformer;

class TrainingType extends AbstractType
{
    private $transformer;

    public function __construct(SkillsToJsonTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 'data_class' => Training::class ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => true],
                'translation_domain' => 'label',
                'label' => 'name',
            ])
            ->add('location', TextType::class, [
                'translation_domain' => 'label',
                'label' => 'location',
            ])
            ->add('duration', TextType::class, [
                'translation_domain' => 'label',
                'label' => 'duration',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['rows' => 8/*, 'class' => 'tinymce'*/],
                'help' => 'help.description',
                'translation_domain' => 'label',
                'label' => 'description',
                'required' => false,
            ])
            ->add('price', TextType::class, [
                'translation_domain' => 'label',
                'label' => 'price',
            ])
            ->add('url', TextType::class, [
                'translation_domain' => 'label',
                'label' => 'url',
            ])
            ->add('startAt', DateTimeType::class, [
                'translation_domain' => 'label',
                'label' => 'start_at',
            ])
            ->add('endAt', DateTimeType::class, [
                'translation_domain' => 'label',
                'label' => 'label.end_at',
            ])
            ->add('isOnline', CheckboxType::class, [
                'translation_domain' => 'label',
                'label' => 'online',
                'required' => false
            ])
            ->add('isOnlineMonitored', CheckboxType::class, [
                'translation_domain' => 'label',
                'label' => 'is_online_monitored',
                'required' => false
            ])
            ->add('isPresential', CheckboxType::class, [
                'translation_domain' => 'label',
                'label' => 'is_presential',
                'required' => false
            ])
            /*->add('trainingSkills', HiddenType::class, [
                'required' => false,
            ])*/
            ->add('save', SubmitType::class, [
                'translation_domain' => 'label',
                'label' => 'create'
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {});

        /*$builder->get('trainingSkills')
            ->addModelTransformer($this->transformer);*/
    }
}