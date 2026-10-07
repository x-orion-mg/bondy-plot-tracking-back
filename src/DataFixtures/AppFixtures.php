<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Impact;
use App\Entity\ImpactCategory;
use App\Entity\Localisation;
use App\Entity\ProjectStatus;
use App\Entity\ProjectType;
use App\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use App\Exception\CustomException;

class AppFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface {

	public static function getGroups(): array {
		return [ 'app' ]; // ✅ Déclaration du groupe "app"
	}

	public function getOrder(): int {
		return 2; // Ordre d'exécution
	}

	/**
	 * @throws Exception
	 */
	public function load( ObjectManager $manager ): void {
		$this->loadProjectTypes( $manager );
		$this->loadProjectStatus( $manager );
		$this->loadImpactCategories( $manager );
		$this->loadImpacts( $manager );
	}

	private function loadProjectTypes( ObjectManager $manager ): void {
		$projectTypes = [
			[
				'label' => 'Reboisement forêt sêche',
				'slug'  => 'reboisement-foret-seche',
			],
			[
				'label' => 'Reforestation de forêts tropicales',
				'slug'  => 'reforestation-de-forets-tropicales',
			],
			[
				'label' => 'Restauration de mangrove',
				'slug'  => 'restauration-de-mangrove',
			],
			[
				'label' => 'Reforestation de forêts de montagne',
				'slug'  => 'reforestation-de-forets-de-montagne',
			]
		];

		// Retrieve all existing project types
		$existingProjectTypes = $manager->getRepository( ProjectType::class )->findAll();
		$projectTypeMap       = [];

		// Create a map of existing project types for quick access
		foreach ( $existingProjectTypes as $projectType ) {
			$projectTypeMap[ $projectType->getSlug() ] = $projectType;
		}

		foreach ( $projectTypes as $typeData ) {
			if ( isset( $projectTypeMap[ $typeData['slug'] ] ) ) {
				// Update the existing project type
				$projectType = $projectTypeMap[ $typeData['slug'] ];
			} else {
				// Create a new project type
				$projectType = new ProjectType();
				$manager->persist( $projectType );
			}

			// Update the properties of the project type
			$projectType->setLabel( $typeData['label'] );
			$projectType->setSlug( $typeData['slug'] );
			$projectType->setColor( '#1B5A2E' ); // Default color
		}

		// Save all changes to the database
		$manager->flush();
	}

	private function loadProjectStatus( ObjectManager $manager ): void {
		$statusProjects = [
			[
				'label' => 'En suivi',
				'slug'  => 'en-suivi',
			],
			[
				'label' => 'Terminé',
				'slug'  => 'termine',
			],
			[
				'label' => 'Planifié',
				'slug'  => 'planifie',
			]
		];

		// Retrieve all existing project statuses
		$existingStatuses = $manager->getRepository( ProjectStatus::class )->findAll();
		$statusMap        = [];

		// Create a map of existing statuses for quick access
		foreach ( $existingStatuses as $status ) {
			$statusMap[ $status->getSlug() ] = $status;
		}

		foreach ( $statusProjects as $statusData ) {
			if ( isset( $statusMap[ $statusData['slug'] ] ) ) {
				// Update the existing status
				$status = $statusMap[ $statusData['slug'] ];
			} else {
				// Create a new status
				$status = new ProjectStatus();
				$manager->persist( $status );
			}

			// Update the properties of the status
			$status->setLabel( $statusData['label'] );
			$status->setSlug( $statusData['slug'] );
		}

		// Save all changes to the database
		$manager->flush();
	}

	private function loadImpactCategories( ObjectManager $manager ): void {
		$impactCategories = [
			'Impact environnemental',
			'Impact social',
			'Impact économique'
		];

		// Retrieve all existing impact categories
		$existingCategories = $manager->getRepository( ImpactCategory::class )->findAll();
		$categoryMap        = [];

		// Create a map of existing categories for quick access
		foreach ( $existingCategories as $category ) {
			$categoryMap[ $category->getName() ] = $category;
		}

		$i = 1;
		foreach ( $impactCategories as $categoryName ) {
			if ( isset( $categoryMap[ $categoryName ] ) ) {
				// Update the existing category
				$category = $categoryMap[ $categoryName ];
			} else {
				// Create a new category
				$category = new ImpactCategory();
				$manager->persist( $category );
			}

			// Update the properties of the category
			$category->setName( $categoryName );
			$category->setEnum( $i );
			$i ++;
		}

		// Save all changes to the database
		$manager->flush();
	}

	/**
	 * @throws CustomException
	 */
	private function loadImpacts( ObjectManager $manager ): void {
		$impacts = [
			[
				'cat_id' => 1,
				'label'  => 'Paysans partenaires',
				'code'   => ''
			],
			[
				'cat_id' => 1,
				'label'  => 'Arbres plantés',
				'code'   => ''
			],
			[
				'cat_id' => 2,
				'label'  => 'Emplois saisonniers',
				'code'   => ''
			],
			[
				'cat_id' => 2,
				'label'  => 'Bénéficiaire indirect',
				'code'   => ''
			],
			[
				'cat_id' => 3,
				'label'  => 'Emplois durable',
				'code'   => ''
			],
			[
				'cat_id' => 3,
				'label'  => 'Hectares restaurés',
				'code'   => ''
			],
		];

		// Retrieve all existing impacts
		$existingImpacts = $manager->getRepository( Impact::class )->findAll();
		$impactMap       = [];

		// Create a map of existing impacts for quick access
		foreach ( $existingImpacts as $impact ) {
			$impactMap[ $impact->getLabel() ] = $impact;
		}

		foreach ( $impacts as $impactData ) {
			if ( isset( $impactMap[ $impactData['label'] ] ) ) {
				// Update the existing impact
				$impactEntity = $impactMap[ $impactData['label'] ];
			} else {
				// Create a new impact
				$impactEntity = new Impact();
				$manager->persist( $impactEntity );
			}

			// Update the properties of the impact
			$impactEntity->setLabel( $impactData['label'] );
			$impactEntity->setCode( $impactData['code'] );

			$catImpact = $manager->getRepository( ImpactCategory::class )->findOneBy( [ 'enum' => $impactData['cat_id'] ] );
			if ( $catImpact ) {
				$impactEntity->setCategory( $catImpact );
			} else {
				throw new CustomException( "Impact Category with ID {$impactData['cat_id']} not found." );
			}
		}

		// Save all changes to the database
		$manager->flush();
	}
}
