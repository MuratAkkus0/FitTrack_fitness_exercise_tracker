<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:test-login',
    description: 'Test login credentials',
)]
class TestLoginCommand extends Command
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
            ->addArgument('password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>User not found with email: ' . $email . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('User found: ' . $user->getEmail());
        $output->writeln('Stored hash: ' . $user->getPassword());

        $isValid = $this->passwordHasher->isPasswordValid($user, $password);

        if ($isValid) {
            $output->writeln('<info>✓ Password is valid!</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>✗ Password is invalid!</error>');
            return Command::FAILURE;
        }
    }
}
