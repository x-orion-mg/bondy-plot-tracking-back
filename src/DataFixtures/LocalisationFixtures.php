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

class LocalisationFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface {

	public static function getGroups(): array {
		return [ 'localisation' ]; // ✅ Déclaration du groupe "app"
	}

	public function getOrder(): int {
		return 1; // Ordre d'exécution
	}

	/**
	 * @throws Exception
	 */
	public function load( ObjectManager $manager ): void {
		$this->loadCountries( $manager );
		$this->loadRegions( $manager );
		$this->loadCities( $manager );
		$this->loadLocalisations( $manager );
	}

	private function loadCountries( ObjectManager $manager ): void {
		$countries = [
			[
				'id'   => 1,
				'code' => 'MG',
				'name' => 'Madagascar',
			],
			[
				'id'   => 2,
				'name' => 'France',
				'code' => 'FR',
			]
		];

		// Récupérer tous les pays existants
		$existingCountries = $manager->getRepository( Country::class )->findAll();
		$countryMap        = [];

		// Créer une carte des pays existants pour un accès rapide
		foreach ( $existingCountries as $country ) {
			$countryMap[ $country->getId() ] = $country;
		}

		foreach ( $countries as $countryData ) {
			if ( isset( $countryMap[ $countryData['id'] ] ) ) {
				// Mettre à jour le pays existant
				$country = $countryMap[ $countryData['id'] ];
			} else {
				// Créer un nouveau pays
				$country = new Country();
				$manager->persist( $country );
			}

			// Mettre à jour les propriétés du pays
			$country->setLabel( $countryData['name'] );
			$country->setId( $countryData['id'] );
			$country->setCode( $countryData['code'] );
		}

		// Enregistrer toutes les modifications en base
		$manager->flush();
	}

	/**
	 * @throws CustomException
	 */
	private function loadRegions( ObjectManager $manager ): void {
		$regions = [
			[
				'region'    => 'Diana',
				'shape_id'  => '27540722B39676432794686',
				'bounds'    => [ [ 47.858215, - 14.265351 ], [ 49.666492, - 11.951969 ] ],
				'latitude'  => 49.03005822243969,
				'longitude' => - 13.483516831734576
			],
			[
				'region'    => 'Sava',
				'shape_id'  => '27540722B96967449701393',
				'bounds'    => [ [ 49.063061, - 15.996834 ], [ 50.486255, - 12.738807 ] ],
				'latitude'  => 49.78547285165044,
				'longitude' => - 14.2835711531378
			],
			[
				'region'    => 'Analanjirofo',
				'shape_id'  => '27540722B29762949035073',
				'bounds'    => [ [ 48.689457, - 17.751877 ], [ 50.046101, - 14.880679 ] ],
				'latitude'  => 49.4236886781961,
				'longitude' => - 16.441917775516785
			],
			[
				'region'    => 'Amoron\'i Mania',
				'shape_id'  => '27540722B26835460634378',
				'bounds'    => [ [ 45.750008, - 21.039229 ], [ 47.761406, - 19.879089 ] ],
				'latitude'  => 46.69795577437185,
				'longitude' => - 20.517141026799848
			],
			[
				'region'    => 'Ihorombe',
				'shape_id'  => '27540722B59079602855311',
				'bounds'    => [ [ 44.977142, - 23.643356 ], [ 47.193503, - 21.624247 ] ],
				'latitude'  => 46.18712689071191,
				'longitude' => - 22.402058714393817
			],
			[
				'region'    => 'Melaky',
				'shape_id'  => '27540722B85261442939952',
				'bounds'    => [ [ 43.928085, - 19.327635 ], [ 45.958294, - 16.183355 ] ],
				'latitude'  => 44.81963015157365,
				'longitude' => - 17.50028696852108
			],
			[
				'region'    => 'Menabe',
				'shape_id'  => '27540722B66660932242608',
				'bounds'    => [ [ 43.547913, - 21.906219 ], [ 45.853382, - 18.165081 ] ],
				'latitude'  => 45.00527812257735,
				'longitude' => - 20.2179442935535
			],
			[
				'region'    => 'Vakinankaratra',
				'shape_id'  => '27540722B16458597550107',
				'bounds'    => [ [ 45.772282, - 20.283323 ], [ 47.855637, - 19.135768 ] ],
				'latitude'  => 46.88954914527835,
				'longitude' => - 19.71531442261184
			],
			[
				'region'    => 'Atsinanana',
				'shape_id'  => '27540722B68428456346892',
				'bounds'    => [ [ 47.647736, - 20.47019 ], [ 49.520143, - 17.484211 ] ],
				'latitude'  => 48.86790487930244,
				'longitude' => - 19.001441871183786
			],
			[
				'region'    => 'Alaotra-Mangoro',
				'shape_id'  => '27540722B20631024421355',
				'bounds'    => [ [ 47.81921, - 19.797498 ], [ 48.904327, - 16.221411 ] ],
				'latitude'  => 48.40947232798999,
				'longitude' => - 17.794566870551378
			],
			[
				'region'    => 'Sofia',
				'shape_id'  => '27540722B38694577181717',
				'bounds'    => [ [ 47.040777, - 16.689133 ], [ 49.447723, - 13.873906 ] ],
				'latitude'  => 48.2796226116304,
				'longitude' => - 15.33798766583594
			],
			[
				'region'    => 'Anosy',
				'shape_id'  => '27540722B69979521515872',
				'bounds'    => [ [ 45.200933, - 25.207525 ], [ 47.417118, - 22.666956 ] ],
				'latitude'  => 46.15255623124139,
				'longitude' => - 23.961584214254543
			],
			[
				'region'    => 'Boeny',
				'shape_id'  => '27540722B20664439648320',
				'bounds'    => [ [ 44.883913, - 17.27705 ], [ 47.364504, - 15.198966 ] ],
				'latitude'  => 46.27266708326505,
				'longitude' => - 16.319249653238302
			],
			[
				'region'    => 'Betsiboka',
				'shape_id'  => '27540722B63964985004787',
				'bounds'    => [ [ 45.494425, - 17.89105 ], [ 48.19702, - 16.298535 ] ],
				'latitude'  => 47.22460631325612,
				'longitude' => - 17.16822898090605
			],
			[
				'region'    => 'Analamanga',
				'shape_id'  => '27540722B10144800929043',
				'bounds'    => [ [ 46.538733, - 19.517239 ], [ 48.04549, - 17.69005 ] ],
				'latitude'  => 47.48429609372721,
				'longitude' => - 18.36267016430078
			],
			[
				'region'    => 'Bongolava',
				'shape_id'  => '27540722B68546220644411',
				'bounds'    => [ [ 45.47538, - 19.467971 ], [ 47.131437, - 17.715393 ] ],
				'latitude'  => 46.16288461907092,
				'longitude' => - 18.53877907769018
			],
			[
				'region'    => 'Itasy',
				'shape_id'  => '27540722B8147071737362',
				'bounds'    => [ [ 46.16641, - 19.441081 ], [ 47.499228, - 18.671477 ] ],
				'latitude'  => 46.914308422347744,
				'longitude' => - 19.029374285411976
			],
			[
				'region'    => 'Atsimo-Andrefana',
				'shape_id'  => '27540722B7206368911185',
				'bounds'    => [ [ 43.220207, - 25.28914 ], [ 45.777157, - 20.892445 ] ],
				'latitude'  => 44.368964158046566,
				'longitude' => - 23.184255666506914
			],
			[
				'region'    => 'Androy',
				'shape_id'  => '27540722B38453733722382',
				'bounds'    => [ [ 44.502134, - 25.6071 ], [ 46.281425, - 23.766229 ] ],
				'latitude'  => 45.45976112828606,
				'longitude' => - 24.734812774478897
			],
			[
				'region'    => 'Atsimo-Atsinanana',
				'shape_id'  => '27540722B82031840289091',
				'bounds'    => [ [ 46.385025, - 24.299276 ], [ 47.96736, - 22.148178 ] ],
				'latitude'  => 47.277841914411766,
				'longitude' => - 23.30358874036274
			],
			[
				'region'    => 'Matsiatra Ambony',
				'shape_id'  => '27540722B35082115892485',
				'bounds'    => [ [ 45.503445, - 22.238323 ], [ 47.458124, - 20.702589 ] ],
				'latitude'  => 46.56805706310331,
				'longitude' => - 21.42237809965785
			],
			[
				'region'    => 'Vatovavy',
				'shape_id'  => '27540722B59950383769216',
				'bounds'    => [ [ 47.331429, - 21.72569 ], [ 48.59936, - 20.211553 ] ],
				'latitude'  => 47.979048861707525,
				'longitude' => - 21.019028036054905
			],
			[
				'region'    => 'Fitovinany',
				'shape_id'  => '27540722B59950383788875',
				'bounds'    => [
					[
						47.094863,
						- 22.503156
					],
					[
						48.206852,
						- 21.382408
					]
				],
				'latitude'  => 47.66737196658417,
				'longitude' => - 21.977639659801923
			]
		];

		// Retrieve all existing regions
		$existingRegions = $manager->getRepository( Region::class )->findAll();
		$regionMap       = [];

		// Create a map of existing regions for quick access
		foreach ( $existingRegions as $region ) {
			$regionMap[ $region->getShapeId() ] = $region;
		}

		foreach ( $regions as $regionData ) {
			if ( isset( $regionMap[ $regionData['shape_id'] ] ) ) {
				// Update the existing region
				$region = $regionMap[ $regionData['shape_id'] ];
			} else {
				// Create a new region
				$region = new Region();
				$manager->persist( $region );
			}

			// Update the properties of the region
			$region->setLabel( $regionData['region'] );
			$region->setShapeId( $regionData['shape_id'] );
			$region->setBounds( $regionData['bounds'] );
			$region->setLatitude( $regionData['latitude'] );
			$region->setLongitude( $regionData['longitude'] );

			$country = $manager->getRepository( Country::class )->find( 1 );
			try {
				$region->setCountry( $country );
			} catch ( \Exception $e ) {
				throw new CustomException( "Country with ID 1 not found.", 0, $e );
			}
		}

		// Save all changes to the database
		$manager->flush();
	}

	/**
	 * @throws CustomException
	 * @throws Exception
	 */
	private function loadCities( ObjectManager $manager ): void {
		// Charger le fichier JSON
		$jsonFilePath = __DIR__ . '/liste_commune_par_region.json';
		$jsonData     = file_get_contents( $jsonFilePath );
		$regionsData  = json_decode( $jsonData, true );

		if ( ! $regionsData || ! isset( $regionsData['Region'] ) ) {
			throw new \Exception( "Erreur lors de la lecture du fichier JSON." );
		}

		// Charger toutes les régions en une seule requête pour optimisation
		$regionRepo = $manager->getRepository( Region::class );
		$regions    = $regionRepo->findAll();
		$regionMap  = [];

		foreach ( $regions as $region ) {
			$regionMap[ strtoupper( $region->getLabel() ) ] = $region;
		}

		foreach ( $regionsData as $regionName => $communes ) {
			// Ignorer la clé "Region" du JSON
			if ( $regionName === "Region" ) {
				continue;
			}

			// Vérifier si la région existe en base de données
			$regionEntity = $regionMap[ strtoupper( $regionName ) ] ?? null;
			if ( ! $regionEntity ) {
				continue; // Ignore la région si elle n'existe pas en base
			}

			// Ajouter les villes à la base
			foreach ( $communes as $city ) {
				$cityEntity    = new City();
				$formattedCity = mb_convert_case( $city, MB_CASE_TITLE, "UTF-8" );
				$cityEntity->setLabel( $formattedCity );
				$cityEntity->setRegion( $regionEntity );

				$manager->persist( $cityEntity );
			}
		}

		// Enregistrer toutes les données en base
		$manager->flush();
	}
	private function loadLocalisations( ObjectManager $manager ): void {
		$localisations = [
			[
				'region'    => 'Diana',
				'shape_id'  => '27540722B39676432794686',
				'bounds'    => [ [ 47.858215, - 14.265351 ], [ 49.666492, - 11.951969 ] ],
				'latitude'  => 49.03005822243969,
				'longitude' => - 13.483516831734576
			],
			[
				'region'    => 'Sava',
				'shape_id'  => '27540722B96967449701393',
				'bounds'    => [ [ 49.063061, - 15.996834 ], [ 50.486255, - 12.738807 ] ],
				'latitude'  => 49.78547285165044,
				'longitude' => - 14.2835711531378
			],
			[
				'region'    => 'Analanjirofo',
				'shape_id'  => '27540722B29762949035073',
				'bounds'    => [ [ 48.689457, - 17.751877 ], [ 50.046101, - 14.880679 ] ],
				'latitude'  => 49.4236886781961,
				'longitude' => - 16.441917775516785
			],
			[
				'region'    => 'Amoron\'i Mania',
				'shape_id'  => '27540722B26835460634378',
				'bounds'    => [ [ 45.750008, - 21.039229 ], [ 47.761406, - 19.879089 ] ],
				'latitude'  => 46.69795577437185,
				'longitude' => - 20.517141026799848
			],
			[
				'region'    => 'Ihorombe',
				'shape_id'  => '27540722B59079602855311',
				'bounds'    => [ [ 44.977142, - 23.643356 ], [ 47.193503, - 21.624247 ] ],
				'latitude'  => 46.18712689071191,
				'longitude' => - 22.402058714393817
			],
			[
				'region'    => 'Melaky',
				'shape_id'  => '27540722B85261442939952',
				'bounds'    => [ [ 43.928085, - 19.327635 ], [ 45.958294, - 16.183355 ] ],
				'latitude'  => 44.81963015157365,
				'longitude' => - 17.50028696852108
			],
			[
				'region'    => 'Menabe',
				'shape_id'  => '27540722B66660932242608',
				'bounds'    => [ [ 43.547913, - 21.906219 ], [ 45.853382, - 18.165081 ] ],
				'latitude'  => 45.00527812257735,
				'longitude' => - 20.2179442935535
			],
			[
				'region'    => 'Vakinankaratra',
				'shape_id'  => '27540722B16458597550107',
				'bounds'    => [ [ 45.772282, - 20.283323 ], [ 47.855637, - 19.135768 ] ],
				'latitude'  => 46.88954914527835,
				'longitude' => - 19.71531442261184
			],
			[
				'region'    => 'Atsinanana',
				'shape_id'  => '27540722B68428456346892',
				'bounds'    => [ [ 47.647736, - 20.47019 ], [ 49.520143, - 17.484211 ] ],
				'latitude'  => 48.86790487930244,
				'longitude' => - 19.001441871183786
			],
			[
				'region'    => 'Alaotra-Mangoro',
				'shape_id'  => '27540722B20631024421355',
				'bounds'    => [ [ 47.81921, - 19.797498 ], [ 48.904327, - 16.221411 ] ],
				'latitude'  => 48.40947232798999,
				'longitude' => - 17.794566870551378
			],
			[
				'region'    => 'Sofia',
				'shape_id'  => '27540722B38694577181717',
				'bounds'    => [ [ 47.040777, - 16.689133 ], [ 49.447723, - 13.873906 ] ],
				'latitude'  => 48.2796226116304,
				'longitude' => - 15.33798766583594
			],
			[
				'region'    => 'Anosy',
				'shape_id'  => '27540722B69979521515872',
				'bounds'    => [ [ 45.200933, - 25.207525 ], [ 47.417118, - 22.666956 ] ],
				'latitude'  => 46.15255623124139,
				'longitude' => - 23.961584214254543
			],
			[
				'region'    => 'Boeny',
				'shape_id'  => '27540722B20664439648320',
				'bounds'    => [ [ 44.883913, - 17.27705 ], [ 47.364504, - 15.198966 ] ],
				'latitude'  => 46.27266708326505,
				'longitude' => - 16.319249653238302
			],
			[
				'region'    => 'Betsiboka',
				'shape_id'  => '27540722B63964985004787',
				'bounds'    => [ [ 45.494425, - 17.89105 ], [ 48.19702, - 16.298535 ] ],
				'latitude'  => 47.22460631325612,
				'longitude' => - 17.16822898090605
			],
			[
				'region'    => 'Analamanga',
				'shape_id'  => '27540722B10144800929043',
				'bounds'    => [ [ 46.538733, - 19.517239 ], [ 48.04549, - 17.69005 ] ],
				'latitude'  => 47.48429609372721,
				'longitude' => - 18.36267016430078
			],
			[
				'region'    => 'Bongolava',
				'shape_id'  => '27540722B68546220644411',
				'bounds'    => [ [ 45.47538, - 19.467971 ], [ 47.131437, - 17.715393 ] ],
				'latitude'  => 46.16288461907092,
				'longitude' => - 18.53877907769018
			],
			[
				'region'    => 'Itasy',
				'shape_id'  => '27540722B8147071737362',
				'bounds'    => [ [ 46.16641, - 19.441081 ], [ 47.499228, - 18.671477 ] ],
				'latitude'  => 46.914308422347744,
				'longitude' => - 19.029374285411976
			],
			[
				'region'    => 'Atsimo-Andrefana',
				'shape_id'  => '27540722B7206368911185',
				'bounds'    => [ [ 43.220207, - 25.28914 ], [ 45.777157, - 20.892445 ] ],
				'latitude'  => 44.368964158046566,
				'longitude' => - 23.184255666506914
			],
			[
				'region'    => 'Androy',
				'shape_id'  => '27540722B38453733722382',
				'bounds'    => [ [ 44.502134, - 25.6071 ], [ 46.281425, - 23.766229 ] ],
				'latitude'  => 45.45976112828606,
				'longitude' => - 24.734812774478897
			],
			[
				'region'    => 'Atsimo-Atsinanana',
				'shape_id'  => '27540722B82031840289091',
				'bounds'    => [ [ 46.385025, - 24.299276 ], [ 47.96736, - 22.148178 ] ],
				'latitude'  => 47.277841914411766,
				'longitude' => - 23.30358874036274
			],
			[
				'region'    => 'Matsiatra Ambony',
				'shape_id'  => '27540722B35082115892485',
				'bounds'    => [ [ 45.503445, - 22.238323 ], [ 47.458124, - 20.702589 ] ],
				'latitude'  => 46.56805706310331,
				'longitude' => - 21.42237809965785
			],
			[
				'region'    => 'Vatovavy',
				'shape_id'  => '27540722B59950383769216',
				'bounds'    => [ [ 47.331429, - 21.72569 ], [ 48.59936, - 20.211553 ] ],
				'latitude'  => 47.979048861707525,
				'longitude' => - 21.019028036054905
			],
			[
				'region'    => 'Fitovinany',
				'shape_id'  => '27540722B59950383788875',
				'bounds'    => [
					[
						47.094863,
						- 22.503156
					],
					[
						48.206852,
						- 21.382408
					],
				],
				'latitude'  => 47.66737196658417,
				'longitude' => - 21.977639659801923
			]
		];

		// Récupérer toutes les localisations existantes
		$existingLocalisations = $manager->getRepository( Localisation::class )->findAll();
		$localisationMap       = [];

		// Créer une carte des localisations existantes pour un accès rapide
		foreach ( $existingLocalisations as $localisation ) {
			$localisationMap[ $localisation->getShapeId() ] = $localisation;
		}

		foreach ( $localisations as $loc ) {
			if ( isset( $localisationMap[ $loc['shape_id'] ] ) ) {
				// Mettre à jour la localisation existante
				$localisation = $localisationMap[ $loc['shape_id'] ];
			} else {
				// Créer une nouvelle localisation
				$localisation = new Localisation();
				$manager->persist( $localisation );
			}

			// Mettre à jour les propriétés de la localisation
			$localisation->setCountry( 'Madagascar' );
			$localisation->setRegion( $loc['region'] );
			$localisation->setShapeId( $loc['shape_id'] );
			$localisation->setBounds( $loc['bounds'] );
			$localisation->setLatitude( $loc['latitude'] );
			$localisation->setLongitude( $loc['longitude'] );
		}

		// Enregistrer toutes les modifications en base
		$manager->flush();
	}
}
