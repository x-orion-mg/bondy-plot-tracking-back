<?php

namespace App\DataFixtures;

use App\Entity\AllocatedTree;
use App\Entity\City;
use App\Entity\Gallery;
use App\Entity\Impact;
use App\Entity\ImpactProject;
use App\Entity\Localisation;
use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\Partner;
use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\ProjectStatus;
use App\Entity\ProjectStep;
use App\Entity\ProjectType;
use App\Entity\Region;
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

class ProjectParentFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface {


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
		return [ 'project-parent' ]; // ✅ Déclaration du groupe "app"
	}

	public function getOrder(): int {
		return 4; // Ordre d'exécution
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
		$this->loadProjectParent( $manager );

	}

	/**
	 * @throws Exception
	 */
	private function loadProjectParent( ObjectManager $manager ): void {
		$status          = [ 1, 2, 3 ];
		$projectsParents = [
			[
				'project_type_id' => 2,
				'city'            => 'Antananarivo',
				'history'         => 'Long history of the Analamanga region. Known for its bustling city of Antananarivo.',
				'title'           => 'City of Antananarivo'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Antsirabe',
				'history'         => 'Famous for hot springs and volcanic soil, Vakinankaratra thrives in agriculture.',
				'title'           => 'Hot Springs of Antsirabe'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Maevatanana',
				'history'         => 'Betsiboka is a region with a mix of plains and rivers, ideal for rice cultivation.',
				'title'           => 'Rice Fields of Maevatanana'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Mahajanga',
				'history'         => 'Known for its port city, Boeny is a bustling region of commerce.',
				'title'           => 'Port City of Mahajanga'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Diego-Suarez',
				'history'         => 'Diana region is famous for its beautiful beaches and tourism.',
				'title'           => 'Beaches of Diego-Suarez'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Fianarantsoa',
				'history'         => 'A historical region with a focus on education and wine production.',
				'title'           => 'Education and Wine of Fianarantsoa'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Toamasina',
				'history'         => 'The main port city of Madagascar with vibrant trade and culture.',
				'title'           => 'Port City of Toamasina'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Toliara',
				'history'         => 'Known for its unique biodiversity and spiny forests.',
				'title'           => 'Biodiversity of Toliara'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Ambatondrazaka',
				'history'         => 'Famous for rice fields and Lake Alaotra.',
				'title'           => 'Rice Fields of Ambatondrazaka'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Moramanga',
				'history'         => 'A hub connecting the east and central parts of Madagascar.',
				'title'           => 'Hub of Moramanga'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Manakara',
				'history'         => 'A coastal city known for its canal and trade routes.',
				'title'           => 'Coastal City of Manakara'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Mananjary',
				'history'         => 'A tranquil region with a focus on vanilla and other crops.',
				'title'           => 'Vanilla Fields of Mananjary'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Farafangana',
				'history'         => 'Known for its serene landscapes and traditional communities.',
				'title'           => 'Serene Landscapes of Farafangana'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Antalaha',
				'history'         => 'Famous for its vanilla production and coastal beauty.',
				'title'           => 'Vanilla Coast of Antalaha'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Sambava',
				'history'         => 'One of the major vanilla-producing areas.',
				'title'           => 'Vanilla Fields of Sambava'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Nosy Be',
				'history'         => 'An island known for its resorts and beaches.',
				'title'           => 'Resorts of Nosy Be'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Morondava',
				'history'         => 'Famous for the Avenue of the Baobabs.',
				'title'           => 'Avenue of the Baobabs'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Betioky',
				'history'         => 'A region known for its agricultural activities.',
				'title'           => 'Agriculture of Betioky'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Fort-Dauphin',
				'history'         => 'A region with stunning beaches and historical sites.',
				'title'           => 'Beaches of Fort-Dauphin'
			],
			[
				'project_type_id' => 2,
				'city'            => 'Taolagnaro',
				'history'         => 'Known for its picturesque landscapes and mining activities.',
				'title'           => 'Mining of Taolagnaro'
			],
			[
				'project_type_id' => 1,
				'city'            => 'Ambanja',
				'history'         => 'A fertile region for cocoa and other crops.',
				'title'           => 'Cocoa Fields of Ambanja'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Ambalavao',
				'history'         => 'Known for its traditional crafts and zebu markets.',
				'title'           => 'Crafts and Zebu of Ambalavao'
			],
			[
				'project_type_id' => 3,
				'city'            => 'Ambalavao',
				'history'         => 'Known for its traditional crafts and zebu markets.',
				'title'           => 'Crafts and Zebu of Ambalavao'
			]
		];

		$i = 0;
		foreach ( $projectsParents as $parent ) {
			$i ++;
			$projectsParent = new ProjectParent();
			$projectsParent->setId( $i );
			$projectsParent->setCity( $parent['city'] );
			$projectsParent->setStartDate( $this->getRandomDate() );
			$projectsParent->setHistory( $parent['history'] );
			$projectsParent->setBannerImage( $this->imageUrls[ array_rand( $this->imageUrls ) ] );
			$projectsParent->setTitle( $parent['title'] );

			// Fetch related entities by ID
			$localisation  = $manager->getRepository( Localisation::class )->find( $i );
			$region        = $manager->getRepository( Region::class )->find( $i );
			$projectStatus = $manager->getRepository( ProjectStatus::class )->find( $status[ array_rand( $status ) ] );
			$projectType   = $manager->getRepository( ProjectType::class )->find( $parent['project_type_id'] );

			$cityId = rand( 1, 18 );
			$city   = $manager->getRepository( City::class )->find( $cityId );

			// Check if related entities are found
			try {
				$projectsParent->setLocalisation( $localisation );
			} catch ( \Exception $e ) {
				throw new CustomException( "Localisation with ID {$parent['localisation_id']} not found.", 0, $e );
			}

			try {
				$projectsParent->setProjectStatus( $projectStatus );
			} catch ( \Exception $e ) {
				throw new CustomException( "ProjectStatus with ID {$parent['project_status_id']} not found.", 0, $e );
			}

			try {
				$projectsParent->setProjectType( $projectType );
			} catch ( \Exception $e ) {
				throw new CustomException( "ProjectType with ID {$parent['project_type_id']} not found.", 0, $e );
			}

			try {
				$projectsParent->SetTown( $city );
			} catch ( \Exception $e ) {
				throw new CustomException( "City with ID {$cityId} not found.", 0, $e );
			}

			try {
				$projectsParent->SetRegion( $region );
			} catch ( \Exception $e ) {
				throw new CustomException( "Region with ID {$region} not found.", 0, $e );
			}

			$manager->persist( $projectsParent );
		}
		$manager->flush();
	}
}
