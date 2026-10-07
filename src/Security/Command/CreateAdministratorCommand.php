<?php

namespace App\Security\Command;

use App\Entity\User; // Remplacez par votre entité User
use App\Entity\Profile;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[AsCommand(
    name: 'app:create-administrator',
    description: 'Creates an administrator user.',
)]
class CreateAdministratorCommand extends Command
{
    protected static $defaultName = 'app:create-admin-user';
    private $validator;
    private $passwordHasher;
    private $em;
    private $profileRepository;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ProfileRepository $profileRepository)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
        $this->profileRepository = $profileRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer les informations
        $email = $io->ask('Saisir l\'adresse e-mail');
        $password = $io->askHidden('Saisir le mot de passe');
        $confirmPassword = $io->askHidden('Confirmer le mot de passe');

        // Validation
        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                //new Assert\Email(),
                new Assert\Regex([
                    'pattern' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/',
                    'message' => 'L\'adresse e-mail doit être au format valide. (bondy@example.com)'
                ])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Le mot de passe doit avoir au moins {{ limit }} caractères',
                ]),
                /* new Assert\Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                    'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère special'
                ]) */
            ],
            'confirmPassword' => [
                new Assert\NotBlank(),
                new Assert\EqualTo([
                    'propertyPath' => 'password',
                    'message' => 'Les deux mots de passe doivent correspondre'
                ])
            ]
        ]);

        $violations = $this->validator->validate([
            'email' => $email,
            'password' => $password,
            'confirmPassword' => $confirmPassword
        ], $constraints);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error($violation->getMessage());
            }
            return Command::FAILURE;
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $profile = $this->profileRepository->getProfileByCode('ADMIN');
        if (is_null($profile)) {
            $profile = new Profile();
            $profile->setLabel('Administrateur');
            $profile->setCode('ROLE_SUPERADMIN');
            $this->em->persist($profile);
        }
        $user->setRoles([$profile->getLabel()]);
        $user->setProfile($profile);

        // Sauvegarder dans la base de données
        $this->em->persist($user);
        $this->em->flush();

        $io->success("L'utilisateur administrateur '{$email}' a été créé avec succès!");

        return Command::SUCCESS;
    }
}
