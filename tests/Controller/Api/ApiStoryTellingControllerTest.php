<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser as KernelBrowserAlias;

class ApiStoryTellingControllerTest extends WebTestCase {
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

	private function assertJsonResponse( array $response, int $expectedStatusCode, string $expectedStatus, array $expectedDataKeys ): void {
		$this->assertEquals( $expectedStatusCode, $response['statusCode'] );
		$this->assertEquals( $expectedStatus, $response['content']['status'] );
		foreach ( $expectedDataKeys as $key ) {
			$this->assertArrayHasKey( $key, $response['content']['data'] );
		}
	}

	public function testShow(): void {
		$storyTellingId = 1; // Assuming the fixture creates a StoryTelling with ID 1

		// Send the GET request
		$response = $this->sendGetRequest( '/api/v1/storytelling/' . $storyTellingId );

		// Expected data structure
		$expectedData = [
			'status' => 'success',
			'data'   => [
				'storyTelling' => [
					'id'          => $storyTellingId,
					'entityType'  => 'App\Entity\ProjectParent',
					'entityID'    => 1,
					'title'       => 'Lancement du Projet Parent 1',
					'bannerImage' => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg',
					'description' => 'Introduction au Projet Parent 1',
					'content'     => 'Ce projet représente un tournant majeur dans notre entreprise. Il s\'agit d\'un projet de grande envergure, visant à améliorer notre infrastructure et à fournir des services de qualité supérieure.',
				],
				'galleries'    => [
					[
						'id'           => 5,
						'entityType'   => 'App\Entity\StoryTelling',
						'entityId'     => 1,
						'caption'      => 'Sous-Projet 2.1 - Phase initiale',
						'type'         => 'image',
						'url'          => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg',
						'thumbnailUrl' => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg'
					],
					[
						'id'           => 6,
						'entityType'   => 'App\Entity\StoryTelling',
						'entityId'     => 1,
						'caption'      => 'Sous-Projet 2.2 - Fin de la construction',
						'type'         => 'image',
						'url'          => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg',
						'thumbnailUrl' => 'https://cdn.pixabay.com/photo/2018/11/11/19/46/christmas-3809544_1280.jpg'
					]
				]
			],
		];

		// Verify the response
		$this->assertJsonResponse( $response, Response::HTTP_OK, 'success', [ 'storyTelling' ] );
		$this->assertEquals( $expectedData, $response['content'] );
	}

	public function testShowNotFound(): void {
		$storyTellingId = 999; // Assuming this ID does not exist
		$response       = $this->sendGetRequest( '/api/v1/storytelling/' . $storyTellingId );

		$this->assertJsonResponse( $response, Response::HTTP_NOT_FOUND, 'failed', [ 'error' ] );
		$this->assertEquals( 'story telling not found', $response['content']['data']['error'] );
	}
}
