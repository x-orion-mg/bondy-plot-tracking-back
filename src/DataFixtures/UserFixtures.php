<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Exception\CustomException;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Profile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;

class UserFixtures extends Fixture implements FixtureGroupInterface,OrderedFixtureInterface  {

	private UserPasswordHasherInterface $passwordHashed;

	private const IMAGE_URL = 'https://img.freepik.com/free-photo/blooming-pohutukawa-tree_181624-48283.jpg';

	public function __construct( UserPasswordHasherInterface $passwordHashed ) {
		$this->passwordHashed = $passwordHashed;
	}

	public function getOrder(): int {
		return 3; // Ordre d'exécution
	}

	public static function getGroups(): array {
		return [ 'user' ]; // ✅ Déclaration du groupe "app"
	}

	/**
	 * @throws Exception
	 */
	public function load( ObjectManager $manager ): void {
		$this->loadRoles( $manager );
		$this->loadProfiles( $manager );
		$this->loadUsers( $manager );
	}

	private function loadRoles( ObjectManager $manager ): void {
		$roles = [
			[
				'label' => 'Créateur de projet',
				'code'  => 'CREATE_PROJECT'
			],
			[
				'label' => 'Mise à jour de projet',
				'code'  => 'UPDATE_PROJECT'
			],
			[
				'label' => 'Suppression de projet',
				'code'  => 'DELETE_PROJECT'
			],
			[
				'label' => 'Création d\'arbe',
				'code'  => 'CREATE_TREE'
			],
			[
				'label' => 'Mise à jour d\'arbe',
				'code'  => 'UPDATE_TREE'
			],
			[
				'label' => 'Suppression d\'arbe',
				'code'  => 'DELETE_TREE'
			],
			[
				'label' => 'Création de parcelle',
				'code'  => 'CREATE_PARCEL'
			],
			[
				'label' => 'Mise à jour de parcelle',
				'code'  => 'UPDATE_PARCEL'
			],
			[
				'label' => 'Suppression de parcelle',
				'code'  => 'DELETE_PARCEL'
			],
			[
				'label' => 'Création de sous-projet',
				'code'  => 'CREATE_SUB_PROJECT'
			],
			[
				'label' => 'Mise à jour de sous-projet',
				'code'  => 'UPDATE_SUB_PROJECT'
			],
			[
				'label' => 'Suppression de sous-projet',
				'code'  => 'DELETE_SUB_PROJECT'
			],
			[
				'label' => 'Création de galerie',
				'code'  => 'CREATE_GALLERY'
			],
			[
				'label' => 'Mise à jour de galerie',
				'code'  => 'UPDATE_GALLERY'
			],
			[
				'label' => 'Suppression de galerie',
				'code'  => 'DELETE_GALLERY'
			],
			[
				'label' => 'Création de storytelling',
				'code'  => 'CREATE_STORYTELLING'
			],
			[
				'label' => 'Mise à jour de storytelling',
				'code'  => 'UPDATE_STORYTELLING',
			],
			[
				'label' => 'Cration d\'espece d\'arbre',
				'code'  => 'CREATE_TREE_SPECIES'
			],
			[
				'label' => 'Mise à jour d\'espece d\'arbre',
				'code'  => 'UPDATE_TREE_SPECIES'
			],
			[
				'label' => 'Suppression d\'espece d\'arbre',
				'code'  => 'DELETE_TREE_SPECIES'
			],
			[
				'label' => 'Création de type d\'utilisation',
				'code'  => 'CREATE_USAGE_TYPE'
			],
			[
				'label' => 'Mise à jour de type d\'utilisation',
				'code'  => 'UPDATE_USAGE_TYPE'
			],
			[
				'label' => 'Suppression de type d\'utilisation',
				'code'  => 'DELETE_USAGE_TYPE'
			],
			[
				'label' => 'Création d\'utilisateur',
				'code'  => 'CREATE_USER',
			],
			[
				'label' => 'Mise à jour d\'utilisateur',
				'code'  => 'UPDATE_USER'
			],
			[
				'label' => 'Suppression d\'utilisateur',
				'code'  => 'DELETE_USER'
			]
		];


		// Retrieve all existing roles
		$existingRoles = $manager->getRepository(Role::class)->findAll();
		$roleMap = [];

		// Create a map of existing roles for quick access
		foreach ($existingRoles as $role) {
			$roleMap[$role->getCode()] = $role;
		}

		foreach ($roles as $roleData) {
			if (isset($roleMap[$roleData['code']])) {
				// Update the existing role
				$roleEntity = $roleMap[$roleData['code']];
			} else {
				// Create a new role
				$roleEntity = new Role();
				$manager->persist($roleEntity);
			}

			// Update the properties of the role
			$roleEntity->setLabel($roleData['label']);
			$roleEntity->setCode($roleData['code']);
		}

		// Save all changes to the database
		$manager->flush();
	}

	private function loadProfiles( ObjectManager $manager ): void {
		$profiles = [
			[
				'label' => 'Administrateur',
				'code'  => 'ADMIN'
			],
			[
				'label' => 'Utilisateur',
				'code'  => 'USER'
			],
			[
				'label' => 'Client Final',
				'code'  => 'CLIENT'
			],
			[
				'label' => 'Partenaire',
				'code'  => 'PARTNER'
			],
			[
				'label' => 'Super Administrateur',
				'code'  => 'SUPER_ADMIN'
			]
		];

		// Retrieve all existing profiles
		$existingProfiles = $manager->getRepository(Profile::class)->findAll();
		$profileMap = [];

		// Create a map of existing profiles for quick access
		foreach ($existingProfiles as $profile) {
			$profileMap[$profile->getCode()] = $profile;
		}

		foreach ($profiles as $profileData) {
			if (isset($profileMap[$profileData['code']])) {
				// Update the existing profile
				$profileEntity = $profileMap[$profileData['code']];
			} else {
				// Create a new profile
				$profileEntity = new Profile();
				$manager->persist($profileEntity);
			}

			// Update the properties of the profile
			$profileEntity->setLabel($profileData['label']);
			$profileEntity->setCode($profileData['code']);
		}

		// Save all changes to the database
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadUsers( ObjectManager $manager ): void {
		$users = [
			[
				'email'      => 'admin@mail.com',
				'profile_id' => 1,
				'lastname'   => 'Admin',
				'firstname'  => 'Admin',
				'roles'     => ['ROLE_SUPERADMIN'],
			],
			[
				'email'      => 'client@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Client',
				'firstname'  => 'Client',
				'roles'     => ['ROLE_USER'],
			],
			[
				'email'      => 'raymond@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Raymond',
				'firstname'  => 'Arnold',
				'roles'     => ['ROLE_USER'],
			],
			[
				'email'      => 'jeanDoe@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Doe',
				'firstname'  => 'Jean',
				'roles'     => ['ROLE_USER'],

			],
			[
				'email'      => 'lopez@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Lopez',
				'firstname'  => 'Jean',
				'roles'     => ['ROLE_USER'],

			],
			[
				'email'      => 'marc@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Marc',
				'firstname'  => 'Jean',
				'roles'     => ['ROLE_USER'],

			],
			[
				'email'      => 'martin.bernard@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Martin',
				'firstname'  => 'Bernard',
				'roles'     => ['ROLE_USER'],

			],
			[
				'email'      => 'thomas.petit@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Thomas',
				'firstname'  => 'Petit',
				'roles'     => ['ROLE_USER'],
			],
			[
				'email'      => 'robert.richard@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Robert',
				'firstname'  => 'Richard',
				'roles'     => ['ROLE_USER'],
			],
			[
				'email'      => 'durand.dubois@mail.com',
				'profile_id' => 3,
				'lastname'   => 'Durand',
				'firstname'  => 'Dubois',
				'roles'     => ['ROLE_USER'],
			],
			[
				'email'      => 'testuser@example.com',
				'profile_id' => 1,
				'lastname'   => 'TestUser',
				'firstname'  => 'User',
				'roles'     => ['ROLE_USER'],
			],
			[
				'lastname'   => 'Partner 1',
				'firstname'  => 'Partner 1',
				'email'      => 'partner1@example.com',
				'profile_id' => 4,
				'phone'      => '1234567890',
				'address'    => '123 Main Street',
				'token'      => 'token1abc',
				'logo'       => 'https://www.bni.mg/assets/themes/bni/img/logo.png',
				'title'      => 'Partner One',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 2',
				'firstname'  => 'Partner 2',
				'profile_id' => 4,
				'email'      => 'partner2@example.com',
				'phone'      => '0987654321',
				'address'    => '456 Elm Street',
				'token'      => 'token2def',
				'logo'       => 'https://cdn.pixabay.com/photo/2017/02/18/19/20/logo-2078018_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 3',
				'firstname'  => 'Partner 3',
				'profile_id' => 4,
				'email'      => 'partner3@example.com',
				'phone'      => '5555555555',
				'address'    => '789 Oak Avenue',
				'token'      => 'token3ghi',
				'logo'       => 'https://cdn.pixabay.com/photo/2023/10/02/08/18/ai-generated-8288689_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 4',
				'firstname'  => 'Partner 4',
				'profile_id' => 4,
				'email'      => 'partner4@example.com',
				'phone'      => '6666666666',
				'address'    => '101 Pine Road',
				'token'      => 'token4jkl',
				'logo'       => 'https://cdn.pixabay.com/photo/2020/09/16/18/39/icon-5577198_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 5',
				'firstname'  => 'Partner 5',
				'profile_id' => 4,
				'email'      => 'partner5@example.com',
				'phone'      => '7777777777',
				'address'    => '202 Maple Lane',
				'token'      => 'token5mno',
				'logo'       => 'https://cdn.pixabay.com/photo/2016/04/01/00/22/cat-1298141_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 6',
				'firstname'  => 'Partner 6',
				'profile_id' => 4,
				'email'      => 'partner6@example.com',
				'phone'      => '8888888888',
				'address'    => '303 Birch Blvd',
				'token'      => 'token6pqr',
				'logo'       => 'https://cdn.pixabay.com/photo/2017/02/15/00/48/logo-2067396_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 7',
				'firstname'  => 'Partner 7',
				'profile_id' => 4,
				'email'      => 'partner7@example.com',
				'phone'      => '9999999999',
				'address'    => '404 Cedar Crescent',
				'token'      => 'token7stu',
				'logo'       => 'https://cdn.pixabay.com/photo/2018/01/06/23/53/knight-3066217_1280.jpg',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 8',
				'firstname'  => 'Partner 8',
				'profile_id' => 4,
				'email'      => 'partner8@example.com',
				'phone'      => '1234432110',
				'address'    => '505 Redwood Drive',
				'token'      => 'token8vwx',
				'logo'       => 'https://cdn.pixabay.com/photo/2023/03/06/13/58/logo-7833520_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 9',
				'firstname'  => 'Partner 9',
				'profile_id' => 4,
				'email'      => 'partner9@example.com',
				'phone'      => '2345678901',
				'address'    => '606 Willow Way',
				'token'      => 'token9yz',
				'logo'       => 'https://cdn.pixabay.com/photo/2024/02/16/10/42/ai-generated-8577256_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
			[
				'lastname'   => 'Partner 10',
				'firstname'  => 'Partner 10',
				'profile_id' => 4,
				'email'      => 'partner10@example.com',
				'phone'      => '3456789012',
				'address'    => '707 Fir Lane',
				'token'      => 'token10abc',
				'logo'       => 'https://cdn.pixabay.com/photo/2023/10/18/22/46/deer-8325201_1280.png',
				'roles'       => ['ROLE_PARTNER'],
			],
		];

		// Retrieve all existing users
		$existingUsers = $manager->getRepository(User::class)->findAll();
		$userMap = [];

		// Create a map of existing users for quick access
		foreach ($existingUsers as $user) {
			$userMap[$user->getEmail()] = $user;
		}

		foreach ($users as $userData) {
			if (isset($userMap[$userData['email']])) {
				// Update the existing user
				$userEntity = $userMap[$userData['email']];
			} else {
				// Create a new user
				$userEntity = new User();
				$manager->persist($userEntity);
			}

			// Update the properties of the user
			$userEntity->setEmail($userData['email']);
			$userEntity->setEnabled(true);
			$userEntity->setLastname($userData['lastname']);
			$userEntity->setFirstname($userData['firstname']);
			$userEntity->setPassword($this->passwordHashed->hashPassword($userEntity, '123456789'));
			$userEntity->setRoles($userData['roles'] ?? ['ROLE_USER']);

			// Fetch related Profile entity
			$profile = $manager->getRepository(Profile::class)->find($userData['profile_id']);
			if ($profile) {
				$userEntity->setProfile($profile);
			} else {
				throw new CustomException("Profile with ID {$userData['profile_id']} not found.");
			}
		}

		// Save all changes to the database
		$manager->flush();
	}
}
