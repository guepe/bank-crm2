<?php

namespace App\Command;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user', description: 'Create a local application user')]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email address')
            ->addOption('role', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Role to assign', ['ROLE_USER'])
            ->addOption('contact-id', null, InputOption::VALUE_REQUIRED, 'Link the user to an existing contact id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $input->getArgument('username');
        $password = (string) $input->getArgument('password');
        $email = $input->getArgument('email');
        $roles = array_values(array_unique(array_filter(array_map('strval', (array) $input->getOption('role')))));
        $contactId = $input->getOption('contact-id');

        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => mb_strtolower(trim($username))]);

        if ($existingUser !== null) {
            $io->error(sprintf('The user "%s" already exists.', $username));

            return Command::FAILURE;
        }

        $user = (new User())
            ->setUsername($username)
            ->setEmail(is_string($email) ? $email : null)
            ->setRoles($roles !== [] ? $roles : ['ROLE_USER']);

        if ($contactId !== null) {
            $contact = $this->entityManager->getRepository(Contact::class)->find((int) $contactId);
            if (!$contact instanceof Contact) {
                $io->error(sprintf('No contact found for id %s.', $contactId));

                return Command::FAILURE;
            }

            if ($contact->getUserAccount() !== null) {
                $io->error(sprintf('Contact %s already has a linked user account.', $contactId));

                return Command::FAILURE;
            }

            $user->setContact($contact);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User "%s" created.', $user->getUserIdentifier()));

        return Command::SUCCESS;
    }
}
