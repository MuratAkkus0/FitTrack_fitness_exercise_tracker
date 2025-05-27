<?php

namespace App\DataFixtures;

use App\Entity\TrainingExercises;
use App\Entity\WorkoutLogDetails;
use App\Entity\WorkoutLogs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WorkoutLogDetailsFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            TrainingExercisesFixtures::class,
            WorkoutLogsFixtures::class
        ];
    }
    public function load(ObjectManager $manager): void
    {
        $workoutLogDetails = new WorkoutLogDetails();
        $workoutLogDetails->setExercise($this->getReference('training_exercise_1', TrainingExercises::class));
        $workoutLogDetails->setWorkoutLog($this->getReference('workout_log_1', WorkoutLogs::class));
        $workoutLogDetails->setNotes('was good');
        $workoutLogDetails->setReps(10);
        $workoutLogDetails->setSets(3);
        $workoutLogDetails->setWeight('15.00');

        $manager->persist($workoutLogDetails);

        // Add to pivot table
        $this->addReference('workout_log_detail_1', $workoutLogDetails);
        $this->getReference('workout_log_1', WorkoutLogs::class)->addWorkoutLogDetail($workoutLogDetails);


        $manager->flush();
    }
}
