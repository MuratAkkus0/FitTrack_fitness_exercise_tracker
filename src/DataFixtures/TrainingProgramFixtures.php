<?php

namespace App\DataFixtures;

use App\Entity\TrainingExercises;
use App\Entity\TrainingProgram;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrainingProgramFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $trainingProgram = new TrainingProgram();
       $trainingProgram->setName('Bad Ass Full Body Program');
       $trainingProgram->setDescription('it is a hard training program from ege fitness.');

       // Add to pivot table
       $this->addReference('training_program_1', $trainingProgram);
        $trainingProgram->addTrainingExercise($this->getReference('training_exercise_1',TrainingExercises::class));

       $manager->persist($trainingProgram);
      
        $manager->flush();
    }
}
