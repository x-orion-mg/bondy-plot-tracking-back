<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Service\CsvProcessorInterface;
use App\Service\CustomerImportService;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CustomerImportControllerTest extends WebTestCase {
	private $customerImportService;
	private $fileService;
	private $csvProcessor;
	private $client;

	protected function setUp(): void {
		$this->customerImportService = $this->createMock( CustomerImportService::class );
		$this->fileService           = $this->createMock( FileService::class );
		$this->csvProcessor          = $this->createMock( CsvProcessorInterface::class );
		$this->entityManager         = $this->createMock( EntityManagerInterface::class );

		$this->client        = static::createClient();
		$this->entityManager = $this->client->getContainer()->get( 'doctrine' )->getManager();
		/** @var User $user */
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'email' => 'testuser@example.com' ] );
		$this->client->loginUser( $user );
	}

	public function testImportInvalidFormatContentFile(): void {
		$this->fileService->method( 'isInvalidFile' )->willReturn( true );
		$originalFilePath = __DIR__ . '/../../test-files/import-user-text-csv.csv';
		$tempFilePath     = sys_get_temp_dir() . '/import-user-text-csv.csv';
		copy( $originalFilePath, $tempFilePath );
		$this->client->request( 'POST', '/admin/users/19/import', [], [
			'csvFile' => new UploadedFile( $tempFilePath, 'import-user-text-csv.csv' )
		] );

		$response = $this->client->getResponse();
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertJson( $response->getContent() );
		$jsonDecode = json_decode( $response->getContent(), true );
		$this->assertFalse( $jsonDecode['success'] );
		$this->assertStringContainsString( 'Le fichier CSV doit contenir les colonnes', $jsonDecode['message'] );
	}

	public function testImportFileAlreadyProcessed(): void {
		$this->fileService->method( 'isInvalidFile' )->willReturn( true );
		$originalFilePath = __DIR__ . '/../../test-files/import-user-valid.csv';
		$tempFilePath     = sys_get_temp_dir() . '/import-user-valid.csv';
		copy( $originalFilePath, $tempFilePath );
		$this->client->request( 'POST', '/admin/users/19/import', [], [
			'csvFile' => new UploadedFile( $tempFilePath, 'import-user-valid.csv' )
		] );

		$response = $this->client->getResponse();
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertStringContainsString( 'Le fichier CSV a déjà été traité.', $response->getContent() );
	}

	public function testImportValidFile(): void {
		$this->fileService->method( 'isInvalidFile' )->willReturn( false );
		$this->customerImportService->method( 'isFileAlreadyProcessed' )->willReturn( false );
		$this->customerImportService->method( 'processFile' )->willReturn( [
			'success' => true,
			'message' => 'Votre fichier a été importé avec succès'
		] );

		$this->fileService->method( 'isInvalidFile' )->willReturn( true );
		$originalFilePath = __DIR__ . '/../../test-files/import-user-valid.csv';
		$tempFilePath     = sys_get_temp_dir() . '/import-user-valid-'.date('now').'.csv';
		copy( $originalFilePath, $tempFilePath );
		$this->client->request( 'POST', '/admin/users/19/import', [], [
			'csvFile' => new UploadedFile( $tempFilePath, 'import-user-valid-'.date('now').'.csv' )
		] );

		$response = $this->client->getResponse();
		$this->assertEquals( 200, $response->getStatusCode() );
		$jsonDecode = json_decode( $response->getContent(), true );
		$this->assertTrue( $jsonDecode['success'] );
		$this->assertStringContainsString( 'Votre fichier a été importé avec succès', $jsonDecode['message'] );
	}
}
