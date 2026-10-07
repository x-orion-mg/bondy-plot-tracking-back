<?php

namespace App\DataFixtures;

use App\Entity\AllocatedTree;
use App\Entity\City;
use App\Entity\Gallery;
use App\Entity\Impact;
use App\Entity\ImpactProject;
use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\ProjectStep;
use App\Entity\StoryTelling;
use App\Entity\SubProject;
use App\Entity\TreeSpecies;
use App\Entity\UsageType;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use App\Exception\CustomException;

class ProjectFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface {


	private array $imageUrls;
	private array $icon;

	private const URL_IMAGE = 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg';
	private const URL_ICON = 'https://zupimages.net/up/25/05/yk9l.png';

	public function __construct() {
		$this->imageUrls = [
			'https://img.freepik.com/free-photo/so-many-vegetables-this-field_181624-18619.jpg',
			'https://img.freepik.com/free-photo/beautiful-shot-lone-tree-field-pink-orange-sky_181624-49472.jpg',
			'https://img.freepik.com/free-photo/blooming-pohutukawa-tree_181624-48283.jpg',
			'https://img.freepik.com/free-photo/two-brown-trees_395237-252.jpg',
			'https://img.freepik.com/free-photo/low-angle-view-tree-covered-green-leaves-sunlight-daytime_181624-21838.jpg',
			'https://cdn.pixabay.com/photo/2013/01/14/08/40/marguerite-74886_1280.jpg',
			'https://cdn.pixabay.com/photo/2016/11/19/15/43/tree-1839959_1280.jpg',
			'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg'
		];
		$this->icon      = [
			'https://zupimages.net/up/25/05/yk9l.png',
		];
	}

	public static function getGroups(): array {
		return [ 'project' ]; // ✅ Déclaration du groupe "app"
	}

	public function getOrder(): int {
		return 5; // Ordre d'exécution
	}

	/**
	 * @throws Exception
	 */
	private function getRandomDate(): DateTime {
		$startTimestamp = strtotime( "2024-01-01" );

		return new DateTime( date( "Y-m-d H:i:s", $startTimestamp ) );
	}

	/**
	 * @throws Exception
	 */
	public function load( ObjectManager $manager ): void {
		$this->loadProjectStep( $manager );
		$this->loadProjectChild( $manager );
		$this->loadParcel( $manager );
		$this->loadUsageType( $manager );
		$this->loadTreeSpecies( $manager );
		$this->loadParcelTreeSpecies( $manager );
		$this->loadGallery( $manager );
		$this->loadStoryTelling( $manager );
		$this->loadSubProject( $manager );
		$this->loadImpactProject( $manager );
		$this->loadAllocatedTree( $manager );
	}


	private function loadProjectStep( ObjectManager $manager ): void {
		$projectSteps = [
			[
				'project_step_name'  => 'Lancement du projet (25%)',
				'project_step_order' => 1,
				'percentage'         => 25
			],
			[
				'project_step_name'  => 'Exécution du projet (50%)',
				'project_step_order' => 2,
				'percentage'         => 50
			],
			[
				'project_step_name'  => 'Suivi et monitoring (75%)',
				'project_step_order' => 3,
				'percentage'         => 75
			],
			[
				'project_step_name'  => 'Fin du projet (100%)',
				'project_step_order' => 4,
				'percentage'         => 100
			],
		];

		// Retrieve all existing project steps
		$existingSteps = $manager->getRepository( ProjectStep::class )->findAll();
		$stepMap       = [];

		// Create a map of existing steps for quick access
		foreach ( $existingSteps as $step ) {
			$stepMap[ $step->getProjectStepName() ] = $step;
		}

		foreach ( $projectSteps as $stepData ) {
			if ( isset( $stepMap[ $stepData['project_step_name'] ] ) ) {
				// Update the existing step
				$projectStep = $stepMap[ $stepData['project_step_name'] ];
			} else {
				// Create a new step
				$projectStep = new ProjectStep();
				$manager->persist( $projectStep );
			}

			// Update the properties of the step
			$projectStep->setProjectStepName( $stepData['project_step_name'] );
			$projectStep->setProjectStepOrder( $stepData['project_step_order'] );
			$projectStep->setPercentage( $stepData['percentage'] );
		}
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadProjectChild( ObjectManager $manager ): void {
		$projectChild = [
			[
				'project_parent_id' => 1,
				'partner_email'        => 'partner1@example.com',
				'project_step_id'   => 1,
				'history'           => 'Début du projet de plantation avec la participation de la communauté locale.',
				'latitude'          => 48.8566,
				'longitude'         => 2.3522,
				'city'              => 'Paris'
			],

			[
				'project_parent_id' => 1,
				'partner_email'        => 'partner1@example.com',
				'project_step_id'   => 2,
				'history'           => 'Sous-projet de reboisement à Lyon, incluant des actions éducatives et communautaires.',
				'latitude'          => 45.7640,
				'longitude'         => 4.8357,
				'city'              => 'Lyon'
			],

			[
				'project_parent_id' => 2,
				'partner_email'        => 'partner2@example.com',
				'project_step_id'   => 3,
				'history'           => 'Projet de restauration d\'habitats naturels à Toulouse, avec une attention particulière à la biodiversité.',
				'latitude'          => 43.6047,
				'longitude'         => 1.4442,
				'city'              => 'Toulouse'
			],

			[
				'project_parent_id' => 3,
				'partner_email'        => 'partner2@example.com',
				'project_step_id'   => 1,
				'history'           => 'Projet de plantation pour améliorer la qualité de l\'air à Bordeaux, avec des actions de sensibilisation.',
				'latitude'          => 44.8378,
				'longitude'         => - 0.5792,
				'city'              => 'Bordeaux'
			],

			[
				'project_parent_id' => 2,
				'partner_email'        => 'partner2@example.com',
				'project_step_id'   => 2,
				'history'           => 'Projet éducatif à Nice, visant à sensibiliser les jeunes à l\'importance de l\'environnement et du reboisement.',
				'latitude'          => 43.7102,
				'longitude'         => 7.2620,
				'city'              => 'Nice'
			],

			[
				'project_parent_id' => 4,
				'partner_email'        => 'partner3@example.com',
				'project_step_id'   => 3,
				'history'           => 'Réhabilitation de forêts urbaines à Nantes, avec des espaces verts pour la communauté.',
				'latitude'          => 47.2184,
				'longitude'         => - 1.5536,
				'city'              => 'Nantes'
			],

			[
				'project_parent_id' => 3,
				'partner_email'        => 'partner3@example.com',
				'project_step_id'   => 1,
				'history'           => 'Projet de restauration écologique dans la région de Toulouse avec un accent sur les espèces locales.',
				'latitude'          => 43.6047,
				'longitude'         => 1.4442,
				'city'              => 'Toulouse'
			],

			[
				'project_parent_id' => 5,
				'partner_email'        => 'partner3@example.com',
				'project_step_id'   => 2,
				'history'           => 'Réaménagement forestier à Marseille, visant à créer des corridors écologiques pour favoriser la biodiversité.',
				'latitude'          => 43.2965,
				'longitude'         => 5.3698,
				'city'              => 'Marseille'
			],
		];

		foreach ( $projectChild as $child ) {
			$projectChildEntity = new ProjectChild();
			$projectChildEntity->setHistory( $child['history'] );
			$projectChildEntity->setTitle( 'Titre ' . date( 'H:i' ) );
			$projectChildEntity->setCity( $child['city'] );
			$projectChildEntity->setBannerImage( self::URL_IMAGE );
			$projectChildEntity->setStartDate( $this->getRandomDate() );
			$projectChildEntity->setEnvironmentalImpactPlantedTrees( 100 );
			$projectChildEntity->setEnvironmentalImpactCO2( 150 );
			$projectChildEntity->setEconomicImpactSustainableEmployment( 99 );
			$projectChildEntity->setEconomicImpactRestoredHectares( 100 );
			$projectChildEntity->setSocialImpactSeasonalJobs( 200 );
			$projectChildEntity->setSocialImpactIndirectBeneficiaries( 600 );
			$projectChildEntity->setLatitude( $child['latitude'] );
			$projectChildEntity->setLongitude( $child['longitude'] );

			// Fetch related entities by ID
			$projectParent = $manager->getRepository( ProjectParent::class )->find( $child['project_parent_id'] );

			$projectStep   = $manager->getRepository( ProjectStep::class )->find( $child['project_step_id'] );
			$cityId        = rand( 1, 18 );
			$city          = $manager->getRepository( City::class )->find( $cityId );

			// Check if related entities are found
			try {
				$projectChildEntity->setProjectParent( $projectParent );
			} catch ( \Exception $e ) {
				throw new CustomException( "ProjectParent with ID {$child['project_parent_id']} not found.", 0, $e );
			}

			try {
				$user  = $manager->getRepository( User::class )->findOneBy( [ 'email' => $child['partner_email'] ] );
				if ( $user ) {
					$projectChildEntity->setUser( $user );
				}
			} catch ( \Exception $e ) {
				throw new CustomException( "Partner with ID {$child['partner_id']} not found.", 0, $e );
			}

			try {
				$projectChildEntity->setProjectStep( $projectStep );
			} catch ( \Exception $e ) {
				throw new CustomException( "ProjectStep with ID {$child['project_step_id']} not found.", 0, $e );
			}

			try {
				$projectChildEntity->setTown( $city );
			} catch ( \Exception $e ) {
				throw new CustomException( "City with ID {$cityId} not found.", 0, $e );
			}

			$manager->persist( $projectChildEntity );
		}

		$manager->flush();

	}

	/**
	 * @throws Exception
	 */
	private function loadParcel( ObjectManager $manager ): void {
		$coordinates = [
			[
				[ 48.215179827529454, 2.3522 ],
				[ 48.20353863436367, 2.3522 ],
				[ 48.20183512105589, 2.3522 ],
				[ 48.21267700511464, 2.3522 ],
				[ 48.22370283552732, 2.3522 ],
				[ 48.227871489608646, 2.3522 ],
				[ 48.227731719116065, 2.3522 ],
				[ 48.215179827529454, 2.3522 ]
			]
		];
		$parcels     = [
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			],
			[
				'delimitation' => json_encode( [
					'type'     => 'Feature',
					'geometry' => [
						'type'        => 'Polygon',
						'coordinates' => $coordinates
					]
				] ),
				'latitude'     => 48.8566,
				'longitude'    => 2.3522,
			]
		];

		$i = 0;
		foreach ( $parcels as $parcel ) {
			$i ++;
			$parcelEntity = new Parcel();
			$parcelEntity->setDelimitation( $parcel['delimitation'] );
			$parcelEntity->setTiffimage( $this->imageUrls[ array_rand( $this->imageUrls ) ] );
			$parcelEntity->setStartDate( $this->getRandomDate() );
			$parcelEntity->setPlantingYear( $this->getRandomDate() );
			$parcelEntity->setLongitude( $parcel['longitude'] );
			$parcelEntity->setLatitude( $parcel['latitude'] );
			$parcelEntity->setTitle( 'Parcel ' . $i );
			$parcelEntity->setBannerImage( $this->imageUrls[ array_rand( $this->imageUrls ) ] );
			$projectChild = $manager->getRepository( ProjectChild::class )->find( $i );

			$projectChild = $manager->getRepository( ProjectChild::class )->find( $i );

			$projectParent = $projectChild->getProjectParent();

			$region = $projectParent->getRegion();

			$cities     = $manager->getRepository( City::class )->findBy( [ 'region' => $region ] );
			$randomCity = $cities[ array_rand( $cities ) ];
			$parcelEntity->setCity( $randomCity->getLabel() );
			$parcelEntity->setTown( $randomCity );

			try {
				$parcelEntity->setProjectChild( $projectChild );
			} catch ( \Exception $e ) {
				throw new CustomException( "ProjectChild with ID {$parcel['project_child_id']} not found.", 0, $e );
			}
			$manager->persist( $parcelEntity );
		}
		$manager->flush();
	}

	private function loadUsageType( ObjectManager $manager ): void {
		$usageTypes = [
			[ 'label' => 'Reboisement' ],
			[ 'label' => 'Agroforesterie' ],
			[ 'label' => 'Forêts protectrices' ],
			[ 'label' => 'Cultures de fruits' ],
			[ 'label' => 'Plantes médicinales' ],
			[ 'label' => 'Plantes indigènes' ],
			[ 'label' => 'Plantes pour la restauration des sols' ],
			[ 'label' => 'Énergies renouvelables (biomasse)' ],
			[ 'label' => 'Éducation et sensibilisation environnementale' ],
			[ 'label' => 'Production de semences' ],
		];

		// Retrieve all existing usage types
		$existingUsageTypes = $manager->getRepository( UsageType::class )->findAll();
		$usageTypeMap       = [];

		// Create a map of existing usage types for quick access
		foreach ( $existingUsageTypes as $usageType ) {
			$usageTypeMap[ $usageType->getLabel() ] = $usageType;
		}

		foreach ( $usageTypes as $typeData ) {
			if ( isset( $usageTypeMap[ $typeData['label'] ] ) ) {
				// Update the existing usage type
				$usageType = $usageTypeMap[ $typeData['label'] ];
			} else {
				// Create a new usage type
				$usageType = new UsageType();
				$manager->persist( $usageType );
			}

			// Update the properties of the usage type
			$usageType->setLabel( $typeData['label'] );
			$usageType->setIcon( $this->icon[ array_rand( $this->icon ) ] );
		}
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadTreeSpecies( ObjectManager $manager ): void {
		$treeSpecies = [
			[
				'usage_type_id'   => 1, // Reboisement
				'common_name'     => 'Chêne',
				'scientific_name' => 'Quercus robur',
				'track_code'      => 'QUERC-R-001',
			],
			[
				'usage_type_id'   => 2, // Agroforesterie
				'common_name'     => 'Manguier',
				'scientific_name' => 'Mangifera indica',
				'track_code'      => 'MANGI-I-002',
			],
			[
				'usage_type_id'   => 3, // Forêts protectrices
				'common_name'     => 'Cèdre de l\'Atlas',
				'scientific_name' => 'Cedrus atlantica',
				'track_code'      => 'CEDRU-A-003',
			],
			[
				'usage_type_id'   => 4, // Cultures de fruits
				'common_name'     => 'Pommier',
				'scientific_name' => 'Malus domestica',
				'track_code'      => 'MALUS-D-004',
			],
			[
				'usage_type_id'   => 5, // Plantes médicinales
				'common_name'     => 'Aloe Vera',
				'scientific_name' => 'Aloe barbadensis miller',
				'track_code'      => 'ALOE-B-005',
			],
			[
				'usage_type_id'   => 6, // Plantes indigènes
				'common_name'     => 'Baobab',
				'scientific_name' => 'Adansonia digitata',
				'track_code'      => 'ADANS-D-006',
			],
			[
				'usage_type_id'   => 7, // Plantes pour la restauration des sols
				'common_name'     => 'Leucaena',
				'scientific_name' => 'Leucaena leucocephala',
				'track_code'      => 'LEUCA-L-007',
			],
			[
				'usage_type_id'   => 8, // Énergies renouvelables (biomasse)
				'common_name'     => 'Eucalyptus',
				'scientific_name' => 'Eucalyptus spp.',
				'track_code'      => 'EUCAL-S-008',
			],
			[
				'usage_type_id'   => 9, // Éducation et sensibilisation environnementale
				'common_name'     => 'Pin sylvestre',
				'scientific_name' => 'Pinus sylvestris',
				'track_code'      => 'PINUS-S-009',
			],
			[
				'usage_type_id'   => 10, // Production de semences
				'common_name'     => 'Acacia',
				'scientific_name' => 'Acacia spp.',
				'track_code'      => 'ACACI-S-010',
			]
		];

		// Retrieve all existing tree species
		$existingTreeSpecies = $manager->getRepository( TreeSpecies::class )->findAll();
		$treeSpeciesMap      = [];

		// Create a map of existing tree species for quick access
		foreach ( $existingTreeSpecies as $species ) {
			$treeSpeciesMap[ $species->getCommonName() ] = $species;
		}

		foreach ( $treeSpecies as $speciesData ) {
			if ( isset( $treeSpeciesMap[ $speciesData['common_name'] ] ) ) {
				// Update the existing tree species
				$treeSpecies = $treeSpeciesMap[ $speciesData['common_name'] ];
			} else {
				// Create a new tree species
				$treeSpecies = new TreeSpecies();
				$manager->persist( $treeSpecies );
			}

			// Update the properties of the tree species
			$treeSpecies->setCommonName( $speciesData['common_name'] );
			$treeSpecies->setScientificName( $speciesData['scientific_name'] );
			$treeSpecies->setTrackCode( $speciesData['track_code'] );
			$treeSpecies->setBanner( $this->imageUrls[ array_rand( $this->imageUrls ) ] );
			$treeSpecies->setIcon( $this->icon[ array_rand( $this->icon ) ] );

			// Fetch related UsageType entity
			$usageType = $manager->getRepository( UsageType::class )->find( $speciesData['usage_type_id'] );
			if ( $usageType ) {
				$treeSpecies->setUsageType( $usageType );
			} else {
				throw new CustomException( "UsageType with ID {$speciesData['usage_type_id']} not found." );
			}
		}
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadParcelTreeSpecies( ObjectManager $manager ): void {
		$parcelTreeSpecies = [
			[
				'parcel_id'             => 1, // ID de la parcelle
				'tree_species_id'       => 1, // ID de l'espèce d'arbre (Chêne)
				'tree_number'           => 5000, // Nombre d'arbres plantés
				'available_tree_number' => 5000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 2, // ID de la parcelle
				'tree_species_id'       => 2, // ID de l'espèce d'arbre (Manguier)
				'tree_number'           => 4000, // Nombre d'arbres plantés
				'available_tree_number' => 4000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 3, // ID de la parcelle
				'tree_species_id'       => 3, // ID de l'espèce d'arbre (Cèdre de l'Atlas)
				'tree_number'           => 3000, // Nombre d'arbres plantés
				'available_tree_number' => 3000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 4, // ID de la parcelle
				'tree_species_id'       => 4, // ID de l'espèce d'arbre (Pommier)
				'tree_number'           => 7000, // Nombre d'arbres plantés
				'available_tree_number' => 7000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 5, // ID de la parcelle
				'tree_species_id'       => 5, // ID de l'espèce d'arbre (Aloe Vera)
				'tree_number'           => 3500, // Nombre d'arbres plantés
				'available_tree_number' => 3500 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 6, // ID de la parcelle
				'tree_species_id'       => 6, // ID de l'espèce d'arbre (Baobab)
				'tree_number'           => 6000, // Nombre d'arbres plantés
				'available_tree_number' => 6000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 7, // ID de la parcelle
				'tree_species_id'       => 7, // ID de l'espèce d'arbre (Leucaena)
				'tree_number'           => 5000, // Nombre d'arbres plantés
				'available_tree_number' => 5000 // Nombre d'arbres disponibles
			],
			[
				'parcel_id'             => 8, // ID de la parcelle
				'tree_species_id'       => 8, // ID de l'espèce d'arbre (Eucalyptus)
				'tree_number'           => 4500, // Nombre d'arbres plantés
				'available_tree_number' => 4500 // Nombre d'arbres disponibles
			],
		];

		foreach ( $parcelTreeSpecies as $tree_species ) {
			$parcelTreeSpecie = new ParcelTreeSpecies();
			$parcelTreeSpecie->setTreeNumber( $tree_species['tree_number'] );
			$parcelTreeSpecie->setAvailableTreeNumber( $tree_species['available_tree_number'] );
			$parcel     = $manager->getRepository( Parcel::class )->find( $tree_species['parcel_id'] );
			$treeSpecie = $manager->getRepository( TreeSpecies::class )->find( $tree_species['tree_species_id'] );
			try {
				$parcelTreeSpecie->setParcel( $parcel );
			} catch ( \Exception $e ) {
				throw new CustomException( "Parcel with ID {$tree_species['parcel_id']} not found.", 0, $e );
			}

			try {
				$parcelTreeSpecie->setTreeSpecies( $treeSpecie );
			} catch ( \Exception $e ) {
				throw new CustomException( "TreeSpecies with ID {$tree_species['tree_species_id']} not found.", 0, $e );
			}
			$manager->persist( $parcelTreeSpecie );
		}
		$manager->flush();
	}

	private function loadGallery( ObjectManager $manager ): void {
		$galleries = [
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 1,
				'caption'     => 'Vue générale du projet parent 1',

			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-projet 1.1 en construction',

			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 1,
				'caption'     => 'Vue de l\'avancement de Sous-Projet 1.2',

			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 1,
				'caption'     => 'Projet parent 2 débuté',

			],
			[
				'entity_type' => StoryTelling::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 2.1 - Phase initiale',

			],
			[
				'entity_type' => StoryTelling::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 2.2 - Fin de la construction',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 1,
				'caption'     => 'Vue d\'ensemble du Projet Parent 3',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 3.1 - Planification',

			],
			[
				'entity_type' => SubProject::class,
				'entity_id'   => 1,
				'caption'     => 'Projet Parent 4 : plan de travail',

			],
			[
				'entity_type' => SubProject::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 4.1 - Site de construction',

			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 4.2 - Projet d\'aménagement',

			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 2,
				'caption'     => 'Vue d\'ensemble du projet Parent 5',

			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 5.1 - Terrassement',

			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 2,
				'caption'     => 'Projet Parent 6 : phase de démarrage',

			],
			[
				'entity_type' => SubProject::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 6.1 - Équipe de conception',

			],
			[
				'entity_type' => SubProject::class,
				'entity_id'   => 2,
				'caption'     => 'Présentation du Projet Parent 7',

			],
			[
				'entity_type' => StoryTelling::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 7.1 - Élaboration des plans',

			],
			[
				'entity_type' => StoryTelling::class,
				'entity_id'   => 2,
				'caption'     => 'Visuel du Projet Parent 8',

			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 8,
				'caption'     => 'Sous-Projet 8.1 - Évaluation des risques 2',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 8.2 - Évaluation des risques',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 1,
				'caption'     => 'Sous-Projet 8.3 - Évaluation des risques',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 8.1 - Évaluation des risques',

			],
			[
				'entity_type' => Parcel::class,
				'entity_id'   => 2,
				'caption'     => 'Sous-Projet 8.1 - Évaluation des risques',

			],
		];

		foreach ( $galleries as $gallery ) {
			$galleryEntity = new Gallery();
			$galleryEntity->setEntityType( $gallery['entity_type'] );
			$galleryEntity->setEntityId( $gallery['entity_id'] );
			$galleryEntity->setCaption( $gallery['caption'] );
			$galleryEntity->setType( 'image' );
			$galleryEntity->setUrl( self::URL_IMAGE );
			$galleryEntity->setThumbnailUrl( self::URL_IMAGE );
			$manager->persist( $galleryEntity );
		}
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadSubProject( ObjectManager $manager ): void {
		$subProjects = [
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 1,
				'name'            => 'Sous-projet 1.1',
				'city'            => 'Paris',
				'history_excerpt' => 'Lancement du sous-projet 1.1',
				'history_content' => 'Ce sous-projet marque le début de notre initiative dans le quartier nord de Paris. Il inclut l\'amélioration des infrastructures existantes et la construction de nouveaux bâtiments.',
				'latitude'        => 48.8566,
				'longitude'       => 2.3522,
			],
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 1,
				'name'            => 'Sous-projet 1.2',
				'city'            => 'Lyon',
				'history_excerpt' => 'Phase de construction',
				'history_content' => 'Sous-projet 1.2 commence à Lyon avec la construction de nouveaux bâtiments commerciaux. Ce projet vise à renforcer notre présence dans la région et à soutenir les besoins en expansion.',
				'latitude'        => 45.7640,
				'longitude'       => 4.8357,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 1,
				'name'            => 'Sous-projet 2.1',
				'city'            => 'Marseille',
				'history_excerpt' => 'Installation des équipements',
				'history_content' => 'Sous-projet 2.1 concerne l\'installation des nouveaux équipements industriels dans la région de Marseille. Le travail consiste à moderniser les installations existantes et à ajouter de nouveaux équipements.',
				'latitude'        => 43.2965,
				'longitude'       => 5.3698,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 1,
				'name'            => 'Sous-projet 2.2',
				'city'            => 'Toulouse',
				'history_excerpt' => 'Aménagement et réaménagement',
				'history_content' => 'Sous-projet 2.2 est dédié à l\'aménagement d\'un nouveau centre de distribution à Toulouse, permettant de renforcer l\'efficacité logistique dans le sud de la France.',
				'latitude'        => 43.6047,
				'longitude'       => 1.4442,
			],
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 3,
				'name'            => 'Sous-projet 3.1',
				'city'            => 'Bordeaux',
				'history_excerpt' => 'Renouvellement d\'infrastructure',
				'history_content' => 'Sous-projet 3.1 se concentre sur le renouvellement de l\'infrastructure existante dans la région de Bordeaux, visant à moderniser les anciennes installations et à améliorer la performance énergétique des bâtiments.',
				'latitude'        => 44.8378,
				'longitude'       => - 0.5792,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 3,
				'name'            => 'Sous-projet 3.2',
				'city'            => 'Nice',
				'history_excerpt' => 'Développement durable',
				'history_content' => 'Sous-projet 3.2 est une initiative dédiée à la création d\'un centre de recyclage et d\'énergie renouvelable à Nice. Ce projet met l\'accent sur la durabilité et l\'impact environnemental positif.',
				'latitude'        => 43.7102,
				'longitude'       => 7.2620,
			],
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 4,
				'name'            => 'Sous-projet 4.1',
				'city'            => 'Nantes',
				'history_excerpt' => 'Création d\'espaces verts',
				'history_content' => 'Sous-projet 4.1 se concentre sur la création de nouveaux espaces verts publics dans la ville de Nantes, visant à améliorer la qualité de vie des résidents et à promouvoir la biodiversité.',
				'latitude'        => 47.2186,
				'longitude'       => - 1.5536,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 4,
				'name'            => 'Sous-projet 4.2',
				'city'            => 'Strasbourg',
				'history_excerpt' => 'Aménagement urbain',
				'history_content' => 'Sous-projet 4.2 est axé sur l\'aménagement urbain de la zone sud de Strasbourg, avec la création de nouvelles infrastructures de transport et la réhabilitation de quartiers anciens.',
				'latitude'        => 48.5734,
				'longitude'       => 7.7521,
			],
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 5,
				'name'            => 'Sous-projet 5.1',
				'city'            => 'Lille',
				'history_excerpt' => 'Énergie renouvelable',
				'history_content' => 'Sous-projet 5.1 vise à déployer des panneaux solaires sur les bâtiments publics de Lille, afin de réduire la consommation d\'énergie et de promouvoir des solutions durables.',
				'latitude'        => 50.6292,
				'longitude'       => 3.0573,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 5,
				'name'            => 'Sous-projet 5.2',
				'city'            => 'Rennes',
				'history_excerpt' => 'Système de transport écologique',
				'history_content' => 'Sous-projet 5.2 se concentre sur la mise en place de solutions de transport écologique à Rennes, notamment l\'introduction de bus électriques et la promotion de la mobilité douce.',
				'latitude'        => 48.1173,
				'longitude'       => - 1.6778,
			],
			[
				'entity_type'     => ProjectParent::class,
				'entity_id'       => 6,
				'name'            => 'Sous-projet 6.1',
				'city'            => 'Le Havre',
				'history_excerpt' => 'Transformation numérique',
				'history_content' => 'Sous-projet 6.1 vise à moderniser les infrastructures numériques de la ville du Havre en mettant en place un réseau de fibre optique et des équipements de gestion de la ville intelligents.',
				'latitude'        => 49.4944,
				'longitude'       => 0.1079,
			],
			[
				'entity_type'     => ProjectChild::class,
				'entity_id'       => 6,
				'name'            => 'Sous-projet 6.2',
				'city'            => 'Tours',
				'history_excerpt' => 'Projets communautaires',
				'history_content' => 'Sous-projet 6.2 se focalise sur des initiatives communautaires à Tours, comme la création de centres d\'accueil pour les jeunes et le soutien aux initiatives culturelles locales.',
				'latitude'        => 47.3444,
				'longitude'       => 0.7083,
			]
		];

		foreach ( $subProjects as $subProject ) {
			$subProjectEntity = new SubProject();
			$subProjectEntity->setEntityType( $subProject['entity_type'] );
			$subProjectEntity->setEntityId( $subProject['entity_id'] );
			$subProjectEntity->setName( $subProject['name'] );
			$subProjectEntity->setBanner( self::URL_IMAGE );
			$subProjectEntity->setStartDate( $this->getRandomDate() );
			$subProjectEntity->setCity( $subProject['city'] );
			$subProjectEntity->setIcon( self::URL_ICON );
			$subProjectEntity->setHistoryExcerpt( $subProject['history_excerpt'] );
			$subProjectEntity->setHistoryContent( $subProject['history_content'] );
			$subProjectEntity->setLatitude( $subProject['latitude'] );
			$subProjectEntity->setLongitude( $subProject['longitude'] );
			$subProjectEntity->setCreatedAt( $this->getRandomDate() );

			$cityId = rand( 1, 18 );
			$city   = $manager->getRepository( City::class )->find( $cityId );

			try {
				$subProjectEntity->setTown( $city );
			} catch ( \Exception $e ) {
				throw new CustomException( "City with ID {$cityId} not found.", 0, $e );
			}

			$manager->persist( $subProjectEntity );
		}
		$manager->flush();

	}

	private function loadStoryTelling( ObjectManager $manager ): void {
		$storyTelling = [
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 1,
				'title'       => 'Lancement du Projet Parent 1',
				'description' => 'Introduction au Projet Parent 1',
				'content'     => 'Ce projet représente un tournant majeur dans notre entreprise. Il s\'agit d\'un projet de grande envergure, visant à améliorer notre infrastructure et à fournir des services de qualité supérieure.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 1,
				'title'       => 'Sous-Projet 1.1 - Étude de faisabilité',
				'description' => 'Sous-Projet 1.1 dédié à l\'étude de faisabilité',
				'content'     => 'Ce sous-projet est destiné à étudier la faisabilité de l\'extension du site principal. Des études environnementales et économiques seront réalisées pour déterminer la viabilité du projet.',
				'created_by'  => 'system',
				'created_at'  => new DateTime(),
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 1,
				'title'       => 'Sous-Projet 1.2 - Design et architecture',
				'description' => 'Sous-Projet 1.2 : phase de design et architecture',
				'content'     => 'Dans cette phase, les plans d\'architecture seront finalisés et les premiers modèles de design seront réalisés. L\'objectif est de préparer le terrain pour la construction.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 2,
				'title'       => 'Inauguration du Projet Parent 2',
				'description' => 'Le Projet Parent 2 entre en phase de construction',
				'content'     => 'Le Projet Parent 2 commence avec une phase d\'infrastructure. Nous avons rencontré des défis logistiques, mais la planification a permis de minimiser les risques.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 2,
				'title'       => 'Sous-Projet 2.1 - Installation des équipements',
				'description' => 'Sous-Projet 2.1 : installation des équipements pour le projet',
				'content'     => 'Sous-Projet 2.1 se concentre sur l\'installation des équipements nécessaires à la mise en place du réseau de distribution d\'énergie. Cela inclut la pose des câbles et des systèmes de stockage.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 2,
				'title'       => 'Sous-Projet 2.2 - Préparation du terrain',
				'description' => 'Sous-Projet 2.2 : préparation du terrain pour la construction',
				'content'     => 'La préparation du terrain comprend l\'excavation, le nivellement et la consolidation du sol. Cela permettra de garantir la stabilité des structures à venir.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 3,
				'title'       => 'Projet Parent 3 - Nouvelle ère',
				'description' => 'Le Projet Parent 3 entame une nouvelle phase',
				'content'     => 'Le Projet Parent 3 représente une transformation majeure pour notre organisation. Nous nous concentrons sur l\'intégration des nouvelles technologies pour améliorer l\'efficacité et la durabilité.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 3,
				'title'       => 'Sous-Projet 3.1 - Recherche et développement',
				'description' => 'Sous-Projet 3.1 : recherche et développement de nouvelles solutions',
				'content'     => 'Sous-Projet 3.1 est centré sur la recherche pour découvrir de nouvelles solutions technologiques. Les chercheurs travailleront sur des innovations qui changeront la donne dans notre secteur.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectParent::class,
				'entity_id'   => 4,
				'title'       => 'Expansion du Projet Parent 4',
				'description' => 'Le Projet Parent 4 est étendu pour couvrir de nouvelles zones',
				'content'     => 'Le Projet Parent 4 se développe. Ce projet vise à agrandir notre couverture géographique en intégrant de nouvelles régions dans nos opérations. L\'objectif est d\'atteindre une croissance durable à long terme.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 4,
				'title'       => 'Sous-Projet 4.1 - Construction des infrastructures',
				'description' => 'Sous-Projet 4.1 : construction des nouvelles infrastructures',
				'content'     => 'Sous-Projet 4.1 consiste à construire de nouvelles infrastructures dans les régions récemment acquises. Les travaux incluent la construction d\'entrepôts et de centres de distribution.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => ProjectChild::class,
				'entity_id'   => 4,
				'title'       => 'Sous-Projet 4.2 - Tests d\'efficacité énergétique',
				'description' => 'Sous-Projet 4.2 : tests d\'efficacité énergétique des bâtiments',
				'content'     => 'Sous-Projet 4.2 s\'attaque à la mise en place de technologies d\'économie d\'énergie pour rendre les bâtiments plus écologiques et efficaces. Des tests rigoureux seront menés sur plusieurs sites.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => 'App\\Entity\\Parcel',
				'entity_id'   => 1,
				'title'       => 'Sous-Projet 4.3 - Tests d\'efficacité énergétique',
				'description' => 'Sous-Projet 4.3 : tests d\'efficacité énergétique des bâtiments',
				'content'     => 'Sous-Projet 4.3 s\'attaque à la mise en place de technologies d\'économie d\'énergie pour rendre les bâtiments plus écologiques et efficaces. Des tests rigoureux seront menés sur plusieurs sites.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			],
			[
				'entity_type' => 'App\\Entity\\Parcel',
				'entity_id'   => 1,
				'title'       => 'Sous-Projet 4.4 - Tests d\'efficacité énergétique',
				'description' => 'Sous-Projet 4.4 : tests d\'efficacité énergétique des bâtiments',
				'content'     => 'Sous-Projet 4.4 s\'attaque à la mise en place de technologies d\'économie d\'énergie pour rendre les bâtiments plus écologiques et efficaces. Des tests rigoureux seront menés sur plusieurs sites.',
				'created_by'  => 'system',
				'created_at'  => new DateTime()
			]
		];

		foreach ( $storyTelling as $story ) {
			$storyTellingEntity = new StoryTelling();
			$storyTellingEntity->setEntityType( $story['entity_type'] );
			$storyTellingEntity->setEntityId( $story['entity_id'] );
			$storyTellingEntity->setTitle( $story['title'] );
			$storyTellingEntity->setDescription( $story['description'] );
			$storyTellingEntity->setContent( $story['content'] );
			$storyTellingEntity->setBannerImage( self::URL_IMAGE );
			$storyTellingEntity->setCreatedAt( $story['created_at'] );
			$manager->persist( $storyTellingEntity );
		}
		$manager->flush();

	}

	private function loadImpactProject( ObjectManager $manager ): void {
		$projectChildren = $manager->getRepository( ProjectChild::class )->findAll();
		$impacts         = $manager->getRepository( Impact::class )->findAll();
		foreach ( $projectChildren as $projectChild ) {
			foreach ( $impacts as $impact ) {
				$impactProject = new ImpactProject();
				$impactProject->setProjectChild( $projectChild );
				$impactProject->setImpact( $impact );
				$impactProject->setValue( rand( 70, 150 ) );

				// Persister l'entité
				$manager->persist( $impactProject );
			}
		}
		$manager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function loadAllocatedTree( ObjectManager $manager ): void {
		$users = [
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10
		];

// Liste des espèces d'arbres (tree_species_id de 1 à 10)
		$tree_species = [
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10
		];

		$parcels = [ 1, 2, 3, 4, 5, 6, 7, 8 ];

// On génère des données pour l'insertion
		$allocated_trees = [];
		foreach ( $users as $user_id ) {
			foreach ( $tree_species as $tree_species_id ) {
				foreach ( $parcels as $parcel_id ) {
					$allocated_trees[] = [
						'user_id'         => $user_id,
						'tree_species_id' => $tree_species_id,
						'parcel_id'       => $parcel_id,
						'track_code'      => 'TRACK-' . $user_id . '-' . $tree_species_id . '-' . $parcel_id
					];
				}
			}
		}

		foreach ( $allocated_trees as $allocated_tree ) {
			$allocatedTreeEntity = new AllocatedTree();
			$user                = $manager->getRepository( User::class )->find( $allocated_tree['user_id'] );
			// Check if related entities are found
			try {
				$allocatedTreeEntity->setUser( $user );
			} catch ( \Exception $e ) {
				throw new CustomException( "User with ID {$allocated_tree['user_id']} not found.", 0, $e );
			}

			$treeSpecies = $manager->getRepository( TreeSpecies::class )->find( $allocated_tree['tree_species_id'] );
			// Check if related entities are found
			try {
				$allocatedTreeEntity->setTreeSpecies( $treeSpecies );
			} catch ( \Exception $e ) {
				throw new CustomException( "Tree Species with ID {$allocated_tree['tree_species_id']} not found.", 0, $e );
			}

			$parcel = $manager->getRepository( Parcel::class )->find( $allocated_tree['parcel_id'] );
			// Check if related entities are found
			try {
				$allocatedTreeEntity->setParcel( $parcel );
			} catch ( \Exception $e ) {
				throw new CustomException( "Parcel with ID {$allocated_tree['parcel_id']} not found.", 0, $e );
			}


			$allocatedTreeEntity->setTrackCode( $allocated_tree['track_code'] );
			$manager->persist( $allocatedTreeEntity );
		}
		$manager->flush();
	}
}
