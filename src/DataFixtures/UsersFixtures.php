<?php

namespace App\DataFixtures;

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
        // Normal kullanıcı
        $user = new Users();
        $user->setName('Murat');
        $user->setSurname('Akkus');
        $user->setEmail('akkusmurat123@gmail.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, "murats123"));
        $user->setRoles([UserRoles::USER->value]);
        $user->setProfileImage('https://cdn.pixabay.com/photo/2016/08/31/11/54/icon-1633249_1280.png');

        $this->addReference('user_1', $user);
        $manager->persist($user);

        // Admin kullanıcısı
        $admin = new Users();
        $admin->setName('Admin');
        $admin->setSurname('User');
        $admin->setEmail('admin@fittrack.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, "admin123"));
        $admin->setRoles([UserRoles::ADMIN->value]);
        $admin->setProfileImage('https://cdn.pixabay.com/photo/2016/08/31/11/54/icon-1633249_1280.png');

        $this->addReference('admin_1', $admin);
        $manager->persist($admin);

        $manager->flush();
    }
}
