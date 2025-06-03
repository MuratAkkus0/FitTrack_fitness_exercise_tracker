<?php

namespace App\Command;

use App\Entity\Users;
use App\Enum\UserRoles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a test user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('name', InputArgument::REQUIRED, 'User first name')
            ->addArgument('surname', InputArgument::REQUIRED, 'User last name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $surname = $input->getArgument('surname');

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln('<error>User already exists with email: ' . $email . '</error>');
            return Command::FAILURE;
        }

        $user = new Users();
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        $user->setRoles([UserRoles::USER->value]);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>User created successfully!</info>');
        $output->writeln('Email: ' . $email);
        $output->writeln('Hashed password: ' . $hashedPassword);

        // Test password immediately
        $isValid = $this->passwordHasher->isPasswordValid($user, $password);
        $output->writeln('Password verification: ' . ($isValid ? '<info>VALID</info>' : '<error>INVALID</error>'));

        return Command::SUCCESS;
    }
}
