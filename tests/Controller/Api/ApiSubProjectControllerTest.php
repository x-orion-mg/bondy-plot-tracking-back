<?php

namespace App\Tests\Controller\Api;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser as KernelBrowserAlias;

class ApiSubProjectControllerTest extends WebTestCase {
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
		$subProjectId = 1; // Assuming the fixture creates a SubProject with ID 1
		$response     = $this->sendGetRequest( '/api/v1/subproject/' . $subProjectId );

		$expectedData = [
			"status" => "success",
			"data"   => [
				"subProjects"  => [
					"id"                 => 1,
					"entityType"         => "App\\Entity\\ProjectParent",
					"entityId"           => 1,
					"name"               => "Sous-projet 1.1",
					"banner"             =>
						"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					"startDate"          => "2024-01-01T00:00:00+00:00",
					"city"               => "Paris",
					"icon"               =>
						"https://zupimages.net/up/25/05/yk9l.png",
					"historyExcerpt"     => "Lancement du sous-projet 1.1",
					"historyContent"     =>
						"Ce sous-projet marque le début de notre initiative dans le quartier nord de Paris. Il inclut l'amélioration des infrastructures existantes et la construction de nouveaux bâtiments.",
					"latitude"           => "48.856600",
					"longitude"          => "2.352200",
					"startDateFormatted" => "Janvier 2024",
				],
				"galleries"    => [
					[
						"id"           => 9,
						"entityType"   => "App\\Entity\\SubProject",
						"entityId"     => 1,
						"caption"      => "Projet Parent 4 : plan de travail",
						"type"         => "image",
						"url"          =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"thumbnailUrl" =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					],
					[
						"id"           => 10,
						"entityType"   => "App\\Entity\\SubProject",
						"entityId"     => 1,
						"caption"      => "Sous-Projet 4.1 - Site de construction",
						"type"         => "image",
						"url"          =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
						"thumbnailUrl" =>
							"https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg",
					],
				],
				"localisation" => [
					"country"   => "Madagascar",
					"region"    => "Diana",
					"shapeId"   => "27540722B39676432794686",
					"bounds"    => [ [ 47.8582154, - 14.2653509 ], [ 49.6664918, - 11.9519693 ] ],
					"latitude"  => "48.762354",
					"longitude" => "-13.108660",
				],
			],
		];

		// Verify the response
		$this->assertJsonResponse( $response, Response::HTTP_OK, 'success', [ 'subProjects' ] );
		$this->assertEquals( $expectedData, $response['content'] );
	}

	public function testShowNotFound(): void {
		$subProjectId = 999; // Assuming this ID does not exist

		$this->client->request( 'GET', '/api/v1/subproject/' . $subProjectId );

		$response = $this->client->getResponse();

		$this->assertResponseStatusCodeSame( Response::HTTP_NOT_FOUND );

		$content = json_decode( $response->getContent(), true );

		$this->assertEquals( 'failed', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'error', $content['data'] );
		$this->assertEquals( 'subproject not found', $content['data']['error'] );
	}
}
