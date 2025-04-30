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
       $trainingExercises = new TrainingExercises();
       $trainingExercises->setName('Bench Press');
       $trainingExercises->setDescription('Flat bench press with barbell');
       $trainingExercises->setTargetMuscleGroup(MuscleGroup::CHEST);

       // Add to pivot table
       $this->addReference('training_exercise_1',$trainingExercises);
     

       $manager->persist($trainingExercises);
      
        $manager->flush();
    }
}
