<?php

namespace App\DataFixtures;

use App\Entity\TrainingProgram;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrainingProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Create a sample training program
        $trainingProgram = new TrainingProgram();
        $trainingProgram->setName('Beginner Full Body Workout');
        $trainingProgram->setDescription('A comprehensive full body workout program for beginners');
        $trainingProgram->setWorkoutsPerWeek(3);
        $trainingProgram->setDurationMinutes(60);
        $trainingProgram->setDifficultyLevel('beginner');
        $trainingProgram->setUsers($this->getReference('user_1', Users::class));
        $trainingProgram->setIsActive(true);

        $this->addReference('training_program_1', $trainingProgram);
        $manager->persist($trainingProgram);

        // Create another training program
        $trainingProgram2 = new TrainingProgram();
        $trainingProgram2->setName('Advanced Strength Training');
        $trainingProgram2->setDescription('High intensity strength training for advanced users');
        $trainingProgram2->setWorkoutsPerWeek(4);
        $trainingProgram2->setDurationMinutes(90);
        $trainingProgram2->setDifficultyLevel('advanced');
        $trainingProgram2->setUsers($this->getReference('user_1', Users::class));
        $trainingProgram2->setIsActive(true);

        $this->addReference('training_program_2', $trainingProgram2);
        $manager->persist($trainingProgram2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
        ];
    }
}
