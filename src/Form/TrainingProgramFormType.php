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
                'label' => 'Program Name',
                'constraints' => [
                    new NotBlank(['message' => 'Program name cannot be empty']),
                    new Length([
                        'min' => 3,
                        'max' => 45,
                        'minMessage' => 'Program name must be at least {{ limit }} characters',
                        'maxMessage' => 'Program name can be at most {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    'placeholder' => 'e.g. Upper Body Workout'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Description can be at most {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    'rows' => 3,
                    'placeholder' => 'Describe your training program...'
                ]
            ])
            ->add('workouts_per_week', ChoiceType::class, [
                'label' => 'Weekly Workout Count',
                'choices' => [
                    '1 day' => 1,
                    '2 days' => 2,
                    '3 days' => 3,
                    '4 days' => 4,
                    '5 days' => 5,
                    '6 days' => 6,
                    '7 days' => 7,
                ],
                'placeholder' => 'Select',
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
                ]
            ])
            ->add('duration_minutes', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 15,
                        'max' => 180,
                        'minMessage' => 'Workout duration must be at least {{ limit }} minutes',
                        'maxMessage' => 'Workout duration can be at most {{ limit }} minutes'
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
                'label' => 'Difficulty Level',
                'choices' => [
                    'Beginner' => 'beginner',
                    'Intermediate' => 'intermediate',
                    'Advanced' => 'advanced',
                    'Expert' => 'expert'
                ],
                'placeholder' => 'Select',
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
                'label' => 'Exercises',
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
