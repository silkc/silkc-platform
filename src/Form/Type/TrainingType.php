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

class TrainingType extends AbstractType
{
    public function __construct()
    {}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 'data_class' => Training::class ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => true],
                'label' => 'label.name',
            ])
            ->add('location', TextType::class, [
                'label' => 'label.location',
            ])
            ->add('duration', TextType::class, [
                'label' => 'label.duration',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['rows' => 8/*, 'class' => 'tinymce'*/],
                'help' => 'help.description',
                'label' => 'label.description',
                'required' => false,
            ])
            ->add('price', TextType::class, [
                'label' => 'label.price',
            ])
            ->add('url', TextType::class, [
                'label' => 'label.url',
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'label.start_at',
                'translation_domain' => 'quiz',
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'label.end_at',
                'translation_domain' => 'quiz',
            ])
            ->add('isOnline', CheckboxType::class, [
                'label' => 'label.online',
                'required' => false
            ])
            ->add('isOnlineMonitored', CheckboxType::class, [
                'label' => 'label.is_online_monitored',
                'required' => false
            ])
            ->add('isPresential', CheckboxType::class, [
                'label' => 'label.is_presential',
                'required' => false
            ])
            ->add('trainingSkills', HiddenType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'label.create'
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /*$item = $event->getData();
                if (null !== $itemLabel = $item->getLabel()) {
                    $item->setSlug($this->slugger->slug($itemLabel)->lower());
                }*/
            });
    }
}