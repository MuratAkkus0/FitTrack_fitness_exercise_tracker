<?php

namespace App\Form;

use App\Entity\TrainingProgram;
use App\Entity\TrainingExercises;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TrainingProgramFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Program Adı',
                'constraints' => [
                    new NotBlank(['message' => 'Program adı boş olamaz']),
                    new Length([
                        'min' => 3,
                        'max' => 45,
                        'minMessage' => 'Program adı en az {{ limit }} karakter olmalıdır',
                        'maxMessage' => 'Program adı en fazla {{ limit }} karakter olabilir'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    'placeholder' => 'Örn: Üst Vücut Antrenmanı'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Açıklama',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Açıklama en fazla {{ limit }} karakter olabilir'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    'rows' => 3,
                    'placeholder' => 'Antrenman programınızı açıklayın...'
                ]
            ])
            ->add('workouts_per_week', ChoiceType::class, [
                'label' => 'Haftalık Antrenman Sayısı',
                'choices' => [
                    '1 gün' => 1,
                    '2 gün' => 2,
                    '3 gün' => 3,
                    '4 gün' => 4,
                    '5 gün' => 5,
                    '6 gün' => 6,
                    '7 gün' => 7,
                ],
                'placeholder' => 'Seçiniz',
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
                ]
            ])
            ->add('duration_minutes', IntegerType::class, [
                'label' => 'Süre (dakika)',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 15,
                        'max' => 180,
                        'minMessage' => 'Antrenman süresi en az {{ limit }} dakika olmalıdır',
                        'maxMessage' => 'Antrenman süresi en fazla {{ limit }} dakika olabilir'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    'placeholder' => '45',
                    'min' => 15,
                    'max' => 180,
                    'step' => 15
                ]
            ])
            ->add('difficulty_level', ChoiceType::class, [
                'label' => 'Zorluk Seviyesi',
                'choices' => [
                    'Başlangıç' => 'beginner',
                    'Orta' => 'intermediate',
                    'İleri' => 'advanced',
                    'Uzman' => 'expert'
                ],
                'placeholder' => 'Seçiniz',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
                ]
            ])
            ->add('training_exercises', EntityType::class, [
                'class' => TrainingExercises::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Egzersizler',
                'required' => false,
                'attr' => [
                    'class' => 'exercise-checkboxes'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrainingProgram::class,
        ]);
    }
}
