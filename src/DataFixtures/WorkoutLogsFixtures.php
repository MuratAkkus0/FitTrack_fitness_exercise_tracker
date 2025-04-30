<?php

namespace App\DataFixtures;

use App\Entity\TrainingProgram;
use App\Entity\Users;
use App\Entity\WorkoutLogDetails;
use App\Entity\WorkoutLogs;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WorkoutLogsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $workoutLogs = new WorkoutLogs();
        $workoutLogs->setUserId($this->getReference('user_1', Users::class));
        $workoutLogs->setCreatedAt(new DateTimeImmutable('now'));
        $workoutLogs->setDuration(0.45);
        $workoutLogs->setIsComplated(true);
        $workoutLogs->setNotes('');
        $workoutLogs->setTrainingProgramId($this->getReference('training_program_1', TrainingProgram::class));

        //Add to pivot table
        // $workoutLogs->addWorkoutLogDetail($this->getReference('workout_log_detail_1', WorkoutLogDetails::class));
        $this->addReference('workout_log_1', $workoutLogs);





        $manager->persist($workoutLogs);

        $manager->flush();
    }
}
