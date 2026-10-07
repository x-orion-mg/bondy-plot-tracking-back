<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser as KernelBrowserAlias;

class ApiProjectChildControllerTest extends WebTestCase {
	private KernelBrowserAlias $client;

	protected function setUp(): void {
		$this->client = static::createClient();
	}

	private function sendGetRequest( string $url ): array {
		$this->client->request( 'GET', $url );
		$response = $this->client->getResponse();

		return [
			'statusCode' => $response->getStatusCode(),
			'content'    => json_decode( $response->getContent(), true )
		];
	}

	private function assertJsonResponse( array $response ): void {
		$expectedDataKeys = [ 'subProjects' ];
		$this->assertEquals( Response::HTTP_OK, $response['statusCode'] );
		$this->assertEquals( 'success', $response['content']['status'] );
		foreach ( $expectedDataKeys as $key ) {
			$this->assertArrayHasKey( $key, $response['content']['data'] );
		}
	}

	public function testShow(): void {
		$projectChildId = 1; // Assuming the fixture creates a ProjectChild with ID 1

		// Send the GET request
		$this->client->request( 'GET', '/api/v1/projects/child/' . $projectChildId );

		$response = $this->client->getResponse();

		// Verify that the response is valid JSON
		$this->assertJson( $response->getContent() );

		// Verify the HTTP status code 200 OK
		$this->assertResponseStatusCodeSame( Response::HTTP_OK );

		// Decode the JSON response
		$content = json_decode( $response->getContent(), true );

		// Verify the structure of the JSON response
		$this->assertEquals( 'success', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'projectChild', $content['data'] );
		$this->assertEquals( $projectChildId, $content['data']['projectChild']['id'] );
		$this->assertArrayHasKey( 'subProjects', $content['data'] );
		$this->assertArrayHasKey( 'galleries', $content['data'] );
		$this->assertArrayHasKey( 'storyTelling', $content['data'] );
	}

	public function testShowNotFound(): void {
		$projectChildId = 999; // Assuming this ID does not exist

		// Send the GET request
		$this->client->request( 'GET', '/api/v1/projects/child/' . $projectChildId );

		$response = $this->client->getResponse();

		// Verify the HTTP status code 404 Not Found
		$this->assertResponseStatusCodeSame( Response::HTTP_NOT_FOUND );

		// Decode the JSON response
		$content = json_decode( $response->getContent(), true );

		// Verify the structure of the JSON response
		$this->assertEquals( 'failed', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'error', $content['data'] );
		$this->assertEquals( 'Project child not found', $content['data']['error'] );
	}

	public function testGetProjectChildByParentAndPartner(): void {
		$parentId  = 1; // Assuming the fixture creates a ProjectParent with ID 1
		$partnerId = 12; // Assuming the fixture creates a Partner with ID 1

		$this->client->request( 'GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child" );
		$response = $this->client->getResponse();
// Verify that the response is valid JSON
		$this->assertJson( $response->getContent() );

		// Verify the HTTP status code 200 OK
		$this->assertResponseStatusCodeSame( Response::HTTP_OK );
		$expectedData = [
			"status" => "success",
			"data"   => [
				"projectChild" => [
					"id"                                  => 1,
					"history"                             =>
						"Début du projet de plantation avec la participation de la communauté locale.",
					"city"                                => "Paris",
					"bannerImage"                         =>
						"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					"startDate"                           => "2024-01-01T00:00:00+00:00",
					"projectParent"                       => [
						"id"           => 1,
						"localisation" => [
							"country"   => "Madagascar",
							"region"    => "Diana",
							"shapeId"   => "27540722B39676432794686",
							"bounds"    => [
								[ 47.8582154, - 14.2653509 ],
								[ 49.6664918, - 11.9519693 ],
							],
							"latitude"  => "48.762354",
							"longitude" => "-13.108660",
						],
					],
					"partner"                             => [
						"id"      => 1,
						"name"    => "Partner 1",
						"email"   => "partner1@example.com",
						"phone"   => "1234567890",
						"address" => "123 Main Street",
						"logo"    => "https://www.bni.mg/assets/themes/bni/img/logo.png",
					],
					"projectStep"                         => [
						"id"               => 1,
						"projectStepName"  => "Lancement du projet (25%)",
						"projectStepOrder" => "1",
					],
					"environmentalImpactPlantedTrees"     => "100",
					'environmentalImpactCO2'              => '150',
					'economicImpactSustainableEmployment' => '99',
					'economicImpactRestoredHectares'      => '100',
					'socialImpactSeasonalJobs'            => '200',
					'socialImpactIndirectBeneficiaries'   => '600',
					"startDateFormatted"                  => "Janvier 2024",
					"latitude"                            => 48.8566,
					"longitude"                           => 2.3522,
				],
				"subProjects"  => [
					[
						"id"                 => 3,
						"entityType"         => "App\\Entity\\ProjectChild",
						"entityId"           => 1,
						"name"               => "Sous-projet 2.1",
						"banner"             =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"startDate"          => '2024-01-01T00:00:00+00:00',
						"city"               => "Marseille",
						"icon"               =>
							"https://zupimages.net/up/25/05/yk9l.png",
						"historyExcerpt"     => "Installation des équipements",
						'historyContent'     => 'Sous-projet 2.1 concerne l\'installation des nouveaux équipements industriels dans la région de Marseille. Le travail consiste à moderniser les installations existantes et à ajouter de nouveaux équipements.',
						"latitude"           => "43.296500",
						"longitude"          => "5.369800",
						"startDateFormatted" => "Janvier 2024",
					],
					[
						"id"                 => 4,
						"entityType"         => "App\\Entity\\ProjectChild",
						"entityId"           => 1,
						"name"               => "Sous-projet 2.2",
						"banner"             =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"startDate"          => '2024-01-01T00:00:00+00:00',
						"city"               => "Toulouse",
						"icon"               =>
							"https://zupimages.net/up/25/05/yk9l.png",
						"historyExcerpt"     => "Aménagement et réaménagement",
						"historyContent"     =>
							'Sous-projet 2.2 est dédié à l\'aménagement d\'un nouveau centre de distribution à Toulouse, permettant de renforcer l\'efficacité logistique dans le sud de la France.',
						"latitude"           => "43.604700",
						"longitude"          => "1.444200",
						"startDateFormatted" => "Janvier 2024",
					],
				],
				"galleries"    => [
					[
						"id"           => 3,
						"entityType"   => "App\\Entity\\ProjectChild",
						"entityId"     => 1,
						"caption"      => 'Vue de l\'avancement de Sous-Projet 1.2',
						"type"         => "image",
						"url"          =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"thumbnailUrl" =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					],
					[
						"id"           => 4,
						"entityType"   => "App\\Entity\\ProjectChild",
						"entityId"     => 1,
						"caption"      => "Projet parent 2 débuté",
						"type"         => "image",
						"url"          =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"thumbnailUrl" =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					],
				],
				"storyTelling" => [
					[
						"id"          => 2,
						"entityType"  => "App\\Entity\\ProjectChild",
						"entityID"    => 1,
						"title"       => "Sous-Projet 1.1 - Étude de faisabilité",
						"bannerImage" => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg',
						"description" =>
							'Sous-Projet 1.1 dédié à l\'étude de faisabilité',
						"content"     =>
							'Ce sous-projet est destiné à étudier la faisabilité de l\'extension du site principal. Des études environnementales et économiques seront réalisées pour déterminer la viabilité du projet.',
					],
					[
						"id"          => 3,
						"entityType"  => "App\\Entity\\ProjectChild",
						"entityID"    => 1,
						"title"       => "Sous-Projet 1.2 - Design et architecture",
						"bannerImage" => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg',
						"description" =>
							"Sous-Projet 1.2 : phase de design et architecture",
						"content"     =>
							'Dans cette phase, les plans d\'architecture seront finalisés et les premiers modèles de design seront réalisés. L\'objectif est de préparer le terrain pour la construction.',
					],
				],
			],
		];
		$content      = json_decode( $response->getContent(), true );
		$this->assertResponseStatusCodeSame( Response::HTTP_OK );
		$this->assertEquals( 'success', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'projectChild', $content['data'] );
		$this->assertArrayHasKey( 'id', $content['data']['projectChild'] );
		$this->assertEquals( 1, $content['data']['projectChild']['id'] );
		$this->assertArrayHasKey( 'history', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'title', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'city', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'bannerImage', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'startDate', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'projectParent', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'user', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'id', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'email', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'firstName', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'lastName', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'logo', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'address', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'phone', $content['data']['projectChild']['user'] );
		$this->assertArrayHasKey( 'id', $content['data']['projectChild']['projectParent'] );
		$this->assertArrayHasKey( 'localisation', $content['data']['projectChild']['projectParent'] );
		$this->assertArrayHasKey( 'country', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'region', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'shapeId', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'bounds', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'latitude', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'longitude', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'longitude', $content['data']['projectChild']['projectParent']['localisation'] );
		$this->assertArrayHasKey( 'projectStep', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'id', $content['data']['projectChild']['projectStep']);
		$this->assertArrayHasKey( 'projectStepName', $content['data']['projectChild']['projectStep']);
		$this->assertArrayHasKey( 'projectStepName', $content['data']['projectChild']['projectStep']);
		$this->assertArrayHasKey( 'projectStepOrder', $content['data']['projectChild']['projectStep']);
		$this->assertArrayHasKey( 'environmentalImpactPlantedTrees', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'environmentalImpactCO2', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'economicImpactSustainableEmployment', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'economicImpactRestoredHectares', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'socialImpactSeasonalJobs', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'socialImpactIndirectBeneficiaries', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'startDateFormatted', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'latitude', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'longitude', $content['data']['projectChild']);
		$this->assertArrayHasKey( 'subProjects', $content['data']);
		$this->assertGreaterThanOrEqual( 2, $content['data']['subProjects']);
		$this->assertArrayHasKey( 'galleries', $content['data']);
		$this->assertGreaterThanOrEqual( 2, $content['data']['galleries']);
		$this->assertArrayHasKey( 'storyTelling', $content['data']);
		$this->assertGreaterThanOrEqual( 2, $content['data']['storyTelling']);
	}

	public function testGetProjectChildByParentAndPartnerNotFound(): void {
		$parentId  = 999; // Assuming this ID does not exist
		$partnerId = 999; // Assuming this ID does not exist

		// Send the GET request
		$this->client->request( 'GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child" );

		$response = $this->client->getResponse();

		// Verify the HTTP status code 404 Not Found
		$this->assertResponseStatusCodeSame( Response::HTTP_NOT_FOUND );

		// Decode the JSON response
		$content = json_decode( $response->getContent(), true );

		// Verify the structure of the JSON response
		$this->assertEquals( 'failed', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'error', $content['data'] );
		$this->assertEquals( 'Project child not found', $content['data']['error'] );
	}
}
