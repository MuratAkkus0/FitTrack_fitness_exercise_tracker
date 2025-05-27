<?php

namespace App\DataFixtures;

use App\Entity\TrainingProgram;
use App\Entity\Users;
use App\Entity\WorkoutLogDetails;
use App\Entity\WorkoutLogs;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WorkoutLogsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $workoutLogs = new WorkoutLogs();
        $workoutLogs->setUser($this->getReference('user_1', Users::class));
        $workoutLogs->setCreatedAt(new DateTimeImmutable('now'));
        $workoutLogs->setDuration('0.45');
        $workoutLogs->setIsCompleted(true);
        $workoutLogs->setNotes('Great workout session!');
        $workoutLogs->setTrainingProgram($this->getReference('training_program_1', TrainingProgram::class));

        //Add to pivot table
        // $workoutLogs->addWorkoutLogDetail($this->getReference('workout_log_detail_1', WorkoutLogDetails::class));
        $this->addReference('workout_log_1', $workoutLogs);

        $manager->persist($workoutLogs);

        // Create another workout log
        $workoutLogs2 = new WorkoutLogs();
        $workoutLogs2->setUser($this->getReference('user_1', Users::class));
        $workoutLogs2->setCreatedAt(new DateTimeImmutable('-1 day'));
        $workoutLogs2->setDuration('0.75');
        $workoutLogs2->setIsCompleted(true);
        $workoutLogs2->setNotes('Challenging but rewarding workout');
        $workoutLogs2->setTrainingProgram($this->getReference('training_program_2', TrainingProgram::class));

        $this->addReference('workout_log_2', $workoutLogs2);
        $manager->persist($workoutLogs2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            TrainingProgramFixtures::class,
        ];
    }
}
