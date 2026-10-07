<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiDashboardControllerTest extends WebTestCase {
	private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

	protected function setUp(): void {
		$this->client = static::createClient();
	}

	public function testDashboardInvalidUuid(): void {
		$this->client->request( 'GET', '/api/v1/dashboard/invalid-uuid/user' );
		$response = $this->client->getResponse();
		// Vérifie que la réponse est bien un objet Response
		$this->assertInstanceOf( Response::class, $response );

		// Vérifie que le code de réponse est 404 (Utilisateur non trouvé)
		$this->assertEquals( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );

		// Vérifie que le message d'erreur est correct
		$responseData = json_decode( $response->getContent(), true );
		$content      = json_decode( $response->getContent(), true );
		$this->assertArrayHasKey( 'status', $content );
		$this->assertEquals( 'failed', $content['status'] );
		$this->assertEquals( 'Invalid UUID format', $content['data']['error'] );
	}

	public function testDashboardUserNotFound(): void {
		$this->client->request( 'GET', '/api/v1/dashboard/939f3fe4-bc06-477b-b124-b3a2245c479f/user' );
		$response = $this->client->getResponse();
		$this->assertInstanceOf( Response::class, $response );

		$this->assertEquals( Response::HTTP_NOT_FOUND, $response->getStatusCode() );

		$content = json_decode( $response->getContent(), true );
		$this->assertArrayHasKey( 'status', $content );
		$this->assertEquals( 'failed', $content['status'] );
		$this->assertEquals( 'User not found', $content['data']['error'] );
	}

	public function testDashboard(): void {
		$user = $this->client->getContainer()->get( 'doctrine' )->getRepository( \App\Entity\User::class )->findOneBy( [ 'email' => 'partner1@example.com' ] );

		$this->client->request( 'GET', '/api/v1/dashboard/' . $user->getUuid() . '/user' );
		$response = $this->client->getResponse();

		$this->assertInstanceOf( Response::class, $response );

		$this->assertEquals( Response::HTTP_OK, $response->getStatusCode() );

		$content = json_decode( $response->getContent(), true );
		$this->assertArrayHasKey( 'status', $content );
		$this->assertEquals( 'success', $content['status'] );
		$this->assertArrayHasKey( 'data', $content );
		$this->assertArrayHasKey( 'user', $content['data'] );
		$this->assertArrayHasKey( 'projectStatus', $content['data'] );
		$this->assertArrayHasKey( 'treeSpecies', $content['data'] );
		$this->assertArrayHasKey( 'totalTreeCount', $content['data'] );
		$this->assertArrayHasKey( 'keyNumber', $content['data'] );
		$this->assertArrayHasKey( 'totalProjectChild', $content['data']['keyNumber'] );
		$this->assertIsInt( $content['data']['keyNumber']['totalProjectChild'] );
		$this->assertArrayHasKey( 'totalRestoredHectares', $content['data']['keyNumber'] );
		$this->assertArrayHasKey( 'totalIndirectBeneficiaries', $content['data']['keyNumber'] );
		$this->assertArrayHasKey( 'totalSeasonalJobsAndSustainableEmployment', $content['data']['keyNumber'] );
		$this->assertArrayHasKey( 'TCO2Sequestered', $content['data'] );
		$this->assertArrayHasKey( 'isCertificate', $content['data'] );
		$this->assertIsBool( $content['data']['isCertificate'] );
		$this->assertArrayHasKey( 'isRapport', $content['data'] );
		$this->assertIsBool( $content['data']['isRapport'] );
	}

}
