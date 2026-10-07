<?php

namespace App\Tests\Controller\Api;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiParcelControllerTest extends WebTestCase {
	private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

	protected function setUp(): void {
		$this->client     = static::createClient();
	}

	public function testShow(): void {
		$parcelId = 1; // Assuming the fixture creates a Parcel with ID 1

		// Envoi de la requête GET
		$this->client->request( 'GET', '/api/v1/projects/parcel/' . $parcelId );

		$response = $this->client->getResponse();

		// Vérifie que la réponse est du JSON valide
		$this->assertJson( $response->getContent() );

		// Vérifie le statut HTTP 200 OK
		$this->assertResponseStatusCodeSame( Response::HTTP_OK );

		// Décodage du JSON
		$content = json_decode( $response->getContent(), true );

		// Vérifications sur la structure de la réponse JSON
		$this->assertEquals( 'success', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'parcel', $content['data'] );
		$this->assertArrayHasKey( 'id', $content['data']['parcel'] );
		$this->assertEquals( $parcelId, $content['data']['parcel']['id'] );
	}

	public function testShowNotFound(): void {
		$parcelId = 999; // Assuming this ID does not exist

		$this->client->request( 'GET', '/api/v1/projects/parcel/' . $parcelId );
		$response = $this->client->getResponse();

		$this->assertInstanceOf( JsonResponse::class, $response );
		$this->assertEquals( 404, $response->getStatusCode() );

		$content = json_decode( $response->getContent(), true );
		$this->assertEquals( 'failed', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'error', $content['data'] );
		$this->assertEquals( 'Parcel not found', $content['data']['error'] );
	}

	public function testGetProjectChildParcels(): void {
		$parentId  = 1; // Assuming the fixture creates a ProjectParent with ID 1
		$partnerId = 12; // Assuming the fixture creates a Partner with ID 1
		$childId   = 1; // Assuming the fixture creates a ProjectChild with ID 1

		// Send the GET request
		$this->client->request( 'GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child/{$childId}/parcel" );

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
		$this->assertEquals( 1, $content['data']['projectChild']['id'] );
		$this->assertArrayHasKey( 'city', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'bannerImage', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'projectParent', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'localisation', $content['data']['projectChild']['projectParent'] );
		$this->assertEquals( 'Madagascar', $content['data']['projectChild']['projectParent']['localisation']['country'] );
		$this->assertEquals( 'Diana', $content['data']['projectChild']['projectParent']['localisation']['region'] );
		$this->assertArrayHasKey( 'startDateFormatted', $content['data']['projectChild'] );
		$this->assertArrayHasKey( 'parcels', $content['data'] );
		$this->assertCount( 1, $content['data']['parcels'] );
		$this->assertArrayHasKey( 'id', $content['data']['parcels'][0] );
		$this->assertEquals( 1, $content['data']['parcels'][0]['id'] );
		$this->assertEquals( 'Parcel 1', $content['data']['parcels'][0]['title'] );
		$this->assertArrayHasKey( 'latitude', $content['data']['parcels'][0] );
		$this->assertArrayHasKey( 'longitude', $content['data']['parcels'][0] );
		$this->assertArrayHasKey( 'delimitationJson', $content['data']['parcels'][0] );
		$this->assertArrayHasKey( 'type', $content['data']['parcels'][0]['delimitationJson'] );
		$this->assertArrayHasKey( 'geometry', $content['data']['parcels'][0]['delimitationJson'] );
		$this->assertArrayHasKey( 'type', $content['data']['parcels'][0]['delimitationJson']['geometry'] );
		$this->assertArrayHasKey( 'coordinates', $content['data']['parcels'][0]['delimitationJson']['geometry'] );

		$this->assertArrayHasKey( 'treeSpecies', $content['data'] );
		$this->assertArrayHasKey( 'id', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'commonName', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'scientificName', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'trackCode', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'banner', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'icon', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'usageType', $content['data']['treeSpecies'][0] );
		$this->assertArrayHasKey( 'treeCount', $content['data']['treeSpecies'][0] );

	}

	public function testGetProjectChildParcelsNotFound(): void {
		$parentId  = 999; // Assuming this ID does not exist
		$partnerId = 999; // Assuming this ID does not exist
		$childId   = 999; // Assuming this ID does not exist

		// Send the GET request
		$this->client->request( 'GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child/{$childId}/parcel" );

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

	public function testGetProjectChildParcel(): void {
		$parentId = 1; // Assuming the fixture creates a ProjectParent with ID 1
		$partnerId = 12; // Assuming the fixture creates a Partner with ID 1
		$childId = 1; // Assuming the fixture creates a ProjectChild with ID 1
		$parcelId = 1; // Assuming the fixture creates a Parcel with ID 1

		// Send the GET request api_project_child_parcel
		$this->client->request('GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child/{$childId}/parcel/{$parcelId}");

		$response = $this->client->getResponse();

		// Verify that the response is valid JSON
		$this->assertJson($response->getContent());

		// Verify the HTTP status code 200 OK
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);

		// Decode the JSON response
		$content = json_decode($response->getContent(), true);

		// Verify the structure of the JSON response
		$this->assertEquals('success', $content['status']);
		$this->assertArrayHasKey('data', $content);
		$this->assertArrayHasKey('parcel', $content['data']);
		$this->assertEquals($parcelId, $content['data']['parcel']['id']);
		$this->assertArrayHasKey('treeSpecies', $content['data']);
		$this->assertArrayHasKey('galleries', $content['data']);
		$this->assertArrayHasKey('storyTelling', $content['data']);
		$this->assertArrayHasKey('localisation', $content['data']);
		$this->assertEquals('Madagascar', $content['data']['localisation']['country']);
		$this->assertEquals('Diana', $content['data']['localisation']['region']);
	}

	public function testGetProjectChildParcelNotFound(): void {
		$parentId = 999; // Assuming this ID does not exist
		$partnerId = 999; // Assuming this ID does not exist
		$childId = 999; // Assuming this ID does not exist
		$parcelId = 999; // Assuming this ID does not exist

		// Send the GET request
		$this->client->request('GET', "/api/v1/projects/parents/{$parentId}/partners/{$partnerId}/child/{$childId}/parcel/{$parcelId}");

		$response = $this->client->getResponse();

		// Verify the HTTP status code 404 Not Found
		$this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

		// Decode the JSON response
		$content = json_decode($response->getContent(), true);

		// Verify the structure of the JSON response
		$this->assertEquals('failed', $content['status']);
		$this->assertArrayHasKey('data', $content);
		$this->assertArrayHasKey('error', $content['data']);
		$this->assertEquals('Project child not found', $content['data']['error']);
	}
}
