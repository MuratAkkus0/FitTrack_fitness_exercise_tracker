<?php

namespace App\DataFixtures;

use App\Entity\TrainingProgram;
use App\Entity\Users;
use App\Enum\UserRoles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new Users();
        $user->setName('Murat');
        $user->setSurname('Akkus');
        $user->setEmail('akkusmurat123@gmail.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, "murats123"));
        $user->setRoles([UserRoles::USER]);
        $user->setProfileImage('https://cdn.pixabay.com/photo/2016/08/31/11/54/icon-1633249_1280.png');

        //Add to pivot table
        $user->addTrainingProgram($this->getReference(
            'training_program_1',
            TrainingProgram::class
        ));
        $this->addReference('user_1', $user);



        $manager->persist($user);

        $manager->flush();
    }
}
