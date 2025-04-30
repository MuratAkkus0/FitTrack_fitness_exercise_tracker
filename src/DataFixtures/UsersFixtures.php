<?php

namespace App\DataFixtures;

use App\Entity\TrainingProgram;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsersFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $user = new Users();
       $user->setName('Murat');
       $user->setSurname('Akkus');
       $user->setEmail('akkusmurat123@gmail.com');
       $user->setPass('testUnhashed');
       $user->setUsername('muratakkus0');
       $user->setProfileImage('https://cdn.pixabay.com/photo/2016/08/31/11/54/icon-1633249_1280.png');

        //Add to pivot table
        $user->addTrainingProgram($this->getReference('training_program_1',
        TrainingProgram::class));
        $this->addReference('user_1',$user);



       $manager->persist($user);

        $manager->flush();
    }
}
