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
            // Chest exercises
            [
                'name' => 'Bench Press',
                'description' => 'Basic strength exercise for chest muscles',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Push-ups',
                'description' => 'Bodyweight chest exercise',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Incline Dumbbell Press',
                'description' => 'Incline press movement for upper chest muscles',
                'muscle_group' => MuscleGroup::CHEST
            ],
            [
                'name' => 'Dips',
                'description' => 'Parallel bar exercise for lower chest and triceps',
                'muscle_group' => MuscleGroup::CHEST
            ],

            // Back exercises
            [
                'name' => 'Pull-ups',
                'description' => 'Basic pulling exercise for back muscles',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Deadlift',
                'description' => 'Compound movement for the entire body',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Bent-over Row',
                'description' => 'Rowing movement for back muscles',
                'muscle_group' => MuscleGroup::BACK
            ],
            [
                'name' => 'Lat Pulldown',
                'description' => 'Pulling exercise for latissimus dorsi muscles',
                'muscle_group' => MuscleGroup::BACK
            ],

            // Leg exercises
            [
                'name' => 'Squats',
                'description' => 'Basic compound movement for leg muscles',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Lunges',
                'description' => 'Functional movement performed on one leg',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Leg Press',
                'description' => 'Machine-based leg press exercise',
                'muscle_group' => MuscleGroup::LEGS
            ],
            [
                'name' => 'Calf Raises',
                'description' => 'Rising movement for calf muscles',
                'muscle_group' => MuscleGroup::LEGS
            ],

            // Shoulder exercises
            [
                'name' => 'Shoulder Press',
                'description' => 'Basic press movement for shoulder muscles',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Lateral Raises',
                'description' => 'Lateral raising movement for side shoulder muscles',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Front Raises',
                'description' => 'Forward raising movement for front shoulder muscles',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],
            [
                'name' => 'Rear Delt Flyes',
                'description' => 'Opening movement for rear shoulder muscles',
                'muscle_group' => MuscleGroup::SHOULDERS
            ],

            // Arm exercises
            [
                'name' => 'Bicep Curls',
                'description' => 'Curling movement for biceps muscles',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Tricep Dips',
                'description' => 'Dip movement for triceps muscles',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Hammer Curls',
                'description' => 'Hammer curl for biceps and forearm muscles',
                'muscle_group' => MuscleGroup::ARMS
            ],
            [
                'name' => 'Close-grip Push-ups',
                'description' => 'Triceps-focused push-up movement',
                'muscle_group' => MuscleGroup::ARMS
            ],

            // Core exercises
            [
                'name' => 'Plank',
                'description' => 'Static hold exercise for core stabilization',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Crunches',
                'description' => 'Basic crunch movement for abdominal muscles',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Russian Twists',
                'description' => 'Twisting movement for oblique muscles',
                'muscle_group' => MuscleGroup::CORE
            ],
            [
                'name' => 'Mountain Climbers',
                'description' => 'Dynamic core and cardiovascular exercise',
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
