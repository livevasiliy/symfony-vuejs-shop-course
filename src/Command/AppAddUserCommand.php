<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class AppAddUserCommand extends Command
{
    protected static $defaultName = 'app-add-user';
    protected static $defaultDescription = 'Create user';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }


    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('email', '', InputArgument::REQUIRED, 'Email')
            ->addOption('password', 'p', InputArgument::REQUIRED, 'Password')
            ->addOption('isAdmin', '', InputArgument::OPTIONAL, 'If set the user is created as an administrator', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-user-command');

        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $isAdmin = $input->getOption('isAdmin');

        $io->title('Add User Command Wizard');
        $io->text(['Please, enter some information']);

        if (!$email) {
            $email = $io->ask('Email');
        }

        if (!$password) {
            $password = $io->askHidden('Password (your type will be hidden)');
        }

        if (!$isAdmin) {
            $question = new Question('Is admin? (1 or 0)', 0);
            $isAdmin = $io->askQuestion($question);
        }

        $isAdmin = boolval($isAdmin);

        try {
            $user = $this->createUser($email, $password, $isAdmin);
        } catch (RuntimeException $runtimeException) {
            $io->comment($runtimeException->getMessage());

            return Command::FAILURE;
        }


        $successMessage = sprintf('%s was successfully created: %s',
            $isAdmin ? 'Administrator' : 'User', $email
        );
        $io->success($successMessage);

        $event = $stopwatch->stop('add-user-command');
        $stopwatchMessage = sprintf('New user\'s id: %s / Elapsed time %.2f ms / Consumed memory: %.2f MB',
            $user->getId(),
            $event->getDuration(),
            $event->getMemory() / 1000 / 1000
        );
        $io->comment($stopwatchMessage);

        return Command::SUCCESS;
    }

    private function createUser(string $email, string $password, bool $isAdmin): User
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        if ($existingUser) {
            throw new RuntimeException('User already exist');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER']);

        $encodePassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encodePassword);

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
