<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Gallery;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class GalleryControllerTest extends WebTestCase {
	private $entityManager;
	private $client;

	protected function setUp(): void {
		$this->entityManager = $this->createMock( EntityManagerInterface::class );

		$this->client        = static::createClient();
		$this->entityManager = $this->client->getContainer()->get( 'doctrine' )->getManager();
		/** @var User $user */
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'email' => 'testuser@example.com' ] );
		$this->client->loginUser( $user );
	}

	private function createAndPersistGallery( array $data ): Gallery {
		$gallery = new Gallery();
		$gallery->setCaption( $data['caption'] ?? 'Test Gallery' );
		$gallery->setEntityType( $data['entityType'] ?? 'App\Entity\ProjectChild' );
		$gallery->setEntityId( $data['entityId'] ?? 1 );
		$gallery->setUrl( $data['url'] ?? 'https://example.com/banner.jpg' );
		$gallery->setThumbnailUrl( $data['thumbnailUrl'] ?? 'https://example.com/banner.jpg' );
		$gallery->setType( $data['type'] ?? 'image' );

		$this->entityManager->persist( $gallery );
		$this->entityManager->flush();

		return $gallery;
	}


	public function testNewPostImage(): void {
		$originalFilePath = __DIR__ . '/../../test-files/banner.jpg';
		$tempFilePath = sys_get_temp_dir() . '/banner_copy.jpg';

		copy($originalFilePath, $tempFilePath);

		$uploadedFile = new UploadedFile(
			$tempFilePath,
			'banner.jpg',
			'image/jpeg',
			null,
			true
		);
		$caption = 'New Gallery - ' . date('Y-m-d H:i:s');

		$data = [
			'caption'      => $caption,
		];

		$this->client->request( 'POST', '/admin/project-child/1/galleries/new', $data, ['uploadFile' => $uploadedFile] );

		$response = $this->client->getResponse();
		$this->assertEquals( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertJson( $response->getContent() );

		$gallery = $this->entityManager->getRepository( Gallery::class )->findOneBy( [ 'caption' => $caption ] );
		$this->assertNotNull( $gallery );
		$this->assertEquals( $caption, $gallery->getCaption() );
		$this->assertStringContainsString( 'uploads/galleries', $gallery->getUrl() );
		$this->assertStringContainsString( 'uploads/galleries/thumbnails', $gallery->getThumbnailUrl() );
		$this->assertEquals( 'image', $gallery->getType() );


	}

	public function testNewPostImage360(): void {
		$originalFilePath = __DIR__ . '/../../test-files/image-360.jpg';
		$tempFilePath = sys_get_temp_dir() . '/image-360.jpg';

		copy($originalFilePath, $tempFilePath);

		$uploadedFile = new UploadedFile(
			$tempFilePath,
			'image-360.jpg',
			'image/jpeg',
			null,
			true
		);
		$caption = 'New Gallery - ' . date('Y-m-d H:i:s');

		$data = [
			'caption'      => $caption,
		];

		$this->client->request( 'POST', '/admin/project-child/1/galleries/new', $data, ['uploadFile' => $uploadedFile] );

		$response = $this->client->getResponse();
		$this->assertEquals( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertJson( $response->getContent() );

		$gallery = $this->entityManager->getRepository( Gallery::class )->findOneBy( [ 'caption' => $caption ] );
		$this->assertNotNull( $gallery );
		$this->assertEquals( $caption, $gallery->getCaption() );
		$this->assertStringContainsString( 'uploads/galleries', $gallery->getUrl() );
		$this->assertStringContainsString( 'uploads/galleries/thumbnails', $gallery->getThumbnailUrl() );
		$this->assertEquals( 'image_360', $gallery->getType() );


	}

	public function testNewPostVideo(): void {
		$originalFilePath = __DIR__ . '/../../test-files/file_example_MP4_480_1_5MG.mp4';
		$tempFilePath = sys_get_temp_dir() . '/file_example_MP4_480_1_5MG.mp4';

		copy($originalFilePath, $tempFilePath);

		$uploadedFile = new UploadedFile(
			$tempFilePath,
			'banner.jpg',
			'video/mp4',
			null,
			true
		);
		$caption = 'New Gallery - ' . date('Y-m-d H:i:s');

		$data = [
			'caption'      => $caption,
		];

		$this->client->request( 'POST', '/admin/project-child/1/galleries/new', $data, ['uploadFile' => $uploadedFile] );

		$response = $this->client->getResponse();
		$this->assertEquals( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertJson( $response->getContent() );

		$gallery = $this->entityManager->getRepository( Gallery::class )->findOneBy( [ 'caption' => $caption ] );
		$this->assertNotNull( $gallery );
		$this->assertEquals( $caption, $gallery->getCaption() );
		$this->assertStringContainsString( 'uploads/galleries', $gallery->getUrl() );
		$this->assertStringContainsString( 'uploads/galleries/thumbnails', $gallery->getThumbnailUrl() );
		$this->assertEquals( 'video', $gallery->getType() );


	}

	public function testDeleteSubProject(): void {
		// Create a new Gallery entity
		$data         = [
			'name'           => 'Test Gallery',
			'entityType'     => 'App\Entity\ProjectChild',
			'entityId'       => 1,
			'historyContent' => 'Test history content',
			'banner'         => 'Test project content',
			'icon'           => 'Test thumbnail',
			'startDate'      => new \DateTime( '2021-01-01' ),
			'city'           => 'Test City',
			'latitude'       => 0,
			'longitude'      => 0,
			'historyExcerpt' => 'Test history excerpt'
		];
		$gallery      = $this->createAndPersistSubProject( $data );
		$subProjectId = $gallery->getId();

		// Send the DELETE request
		$this->client->request( 'DELETE', '/admin/project-parent/1/gallery/' . $subProjectId . '/delete' );

		// Verify the response
		$response = $this->client->getResponse();

		$this->assertJson( $response->getContent() );

		$this->assertJsonStringEqualsJsonString(
			json_encode( [ 'success' => true ] ),
			$response->getContent()
		);

		// Verify the entity has been deleted
		$deletedSubProject = $this->entityManager->getRepository( Gallery::class )->find( $subProjectId );
		$this->assertNull( $deletedSubProject );
	}

	public function testDeleteNonExistentSubProject(): void {


		// Attempt to delete a non-existent Gallery
		$nonExistentSubProjectId = 9999; // Assuming this ID does not exist in the database

		// Send the DELETE request
		$this->client->request( 'DELETE', '/admin/project-parent/1/gallery/' . $nonExistentSubProjectId . '/delete' );

		// Verify the response
		$response = $this->client->getResponse();

		// Check if the response is JSON
		$this->assertJson( $response->getContent() );

		// Verify the response content
		$this->assertJsonStringEqualsJsonString(
			json_encode( [ 'success' => false, 'message' => 'Gallery not found' ] ),
			$response->getContent()
		);

		// Verify the status code
		$this->assertEquals( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
	}
}
