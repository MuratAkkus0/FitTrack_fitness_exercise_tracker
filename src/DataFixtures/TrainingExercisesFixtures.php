<?php

namespace App\DataFixtures;

use App\Entity\TrainingExercises;
use App\Enum\MuscleGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrainingExercisesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $exercises = [
            // Göğüs egzersizleri
            [
                'name' => 'Bench Press',
                'description' => 'Göğüs kasları için temel kuvvet egzersizi',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Push-ups',
                'description' => 'Vücut ağırlığı ile yapılan göğüs egzersizi',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Incline Dumbbell Press',
                'description' => 'Üst göğüs kasları için eğimli press hareketi',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Dips',
                'description' => 'Alt göğüs ve triceps için paralel bar egzersizi',
                'muscle_group' => MuscleGroup::CHEST
            ],

            // Sırt egzersizleri
            [
                'name' => 'Pull-ups',
                'description' => 'Sırt kasları için temel çekme egzersizi',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Deadlift',
                'description' => 'Tüm vücut için compound hareket',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Bent-over Row',
                'description' => 'Sırt kasları için kürek çekme hareketi',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Lat Pulldown',
                'description' => 'Latissimus dorsi kasları için çekme egzersizi',
                'muscle_group' => MuscleGroup::BACK
            ],

            // Bacak egzersizleri
            [
                'name' => 'Squats',
                'description' => 'Bacak kasları için temel compound hareket',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Lunges',
                'description' => 'Tek bacak üzerinde yapılan fonksiyonel hareket',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Leg Press',
                'description' => 'Makine ile yapılan bacak press egzersizi',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Calf Raises',
                'description' => 'Baldır kasları için yükselme hareketi',
                'muscle_group' => MuscleGroup::LEGS
            ],

            // Omuz egzersizleri
            [
                'name' => 'Shoulder Press',
                'description' => 'Omuz kasları için temel press hareketi',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Lateral Raises',
                'description' => 'Yan omuz kasları için yanlara açma hareketi',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Front Raises',
                'description' => 'Ön omuz kasları için öne kaldırma hareketi',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Rear Delt Flyes',
                'description' => 'Arka omuz kasları için açma hareketi',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],

            // Kol egzersizleri
            [
                'name' => 'Bicep Curls',
                'description' => 'Biceps kasları için kıvırma hareketi',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Tricep Dips',
                'description' => 'Triceps kasları için dip hareketi',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Hammer Curls',
                'description' => 'Biceps ve önkol kasları için çekiç curl',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Close-grip Push-ups',
                'description' => 'Triceps odaklı şınav hareketi',
                'muscle_group' => MuscleGroup::ARMS
            ],

            // Core egzersizleri
            [
                'name' => 'Plank',
                'description' => 'Core stabilizasyonu için statik tutma egzersizi',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Crunches',
                'description' => 'Karın kasları için temel mekik hareketi',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Russian Twists',
                'description' => 'Yan karın kasları için döndürme hareketi',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Mountain Climbers',
                'description' => 'Dinamik core ve kardiyovasküler egzersiz',
                'muscle_group' => MuscleGroup::CORE
            ],
        ];

        foreach ($exercises as $index => $exerciseData) {
            $exercise = new TrainingExercises();
            $exercise->setName($exerciseData['name']);
            $exercise->setDescription($exerciseData['description']);
            $exercise->setTargetMuscleGroup($exerciseData['muscle_group']);

            // Add reference for the first few exercises
            if ($index < 10) {
                $this->addReference('training_exercise_' . ($index + 1), $exercise);
            }

            $manager->persist($exercise);
        }

        $manager->flush();
    }
}
