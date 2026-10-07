<?php

namespace App\Tests\Controller\Admin;

use App\Entity\StoryTelling;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubProjectControllerTest extends WebTestCase {
    private $entityManager;
    private $client;

    protected function setUp(): void {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->client = static::createClient();
	    $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
	    /** @var User $user */
	    $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'testuser@example.com']);
	    $this->client->loginUser($user);
    }

	private function createAndPersistSubProject(array $data): StoryTelling {
		$storyTelling = new StoryTelling();
		$storyTelling->setTitle($data['title'] ?? 'Test StoryTelling');
		$storyTelling->setEntityType($data['entityType'] ?? 'App\Entity\ProjectChild');
		$storyTelling->setEntityId($data['entityId'] ?? 1);
		$storyTelling->setBannerImage($data['banner'] ?? 'https://example.com/banner.jpg');
		$storyTelling->setDescription($data['description'] ?? 'Test description');
		$storyTelling->setContent($data['content'] ?? 'Test content');

		$this->entityManager->persist($storyTelling);
		$this->entityManager->flush();

		return $storyTelling;
	}

	public function testNewGet(): void {
		// Send the GET request to the new StoryTelling route
		$this->client->request('GET', '/admin/project-child/1/subprojects/new');

		// Verify the response
		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertStringContainsString('Nom du sous-projet', $response->getContent());
	}

	public function testNewPost(): void {
		// Data for the new StoryTelling
		$data = [
			'name' => 'New StoryTelling',
			'city' => 'New City',
			'historyExcerpt' => 'New history excerpt',
			'historyContent' => 'New history content',
			'longitude' => 10,
			'latitude' => 20,
			'startDate' => '01/01/2022'
		];

		$this->client->request('POST', '/admin/project-child/1/subprojects/new', $data);

		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertJson($response->getContent());

		$storyTelling = $this->entityManager->getRepository(StoryTelling::class)->findOneBy(['name' => 'New StoryTelling']);
		$this->assertNotNull($storyTelling);
		$this->assertEquals('New City', $storyTelling->getCity());
		$this->assertEquals('New history excerpt', $storyTelling->getHistoryExcerpt());
		$this->assertEquals('New history content', $storyTelling->getHistoryContent());
		$this->assertEquals(10, $storyTelling->getLongitude());
		$this->assertEquals(20, $storyTelling->getLatitude());

		// Ensure the startDate is set to the expected date
		$expectedDate = new \DateTime('2022-01-01 00:00:00');
		$actualDate = $storyTelling->getStartDate();
		$actualDate->setTime(0, 0, 0); // Set the time to midnight
		$this->assertEquals($expectedDate, $actualDate);
	}

	public function testEdit(): void {
		// Create a new StoryTelling entity
		$data = [
			'name' => 'Test StoryTelling',
			'entityType' => 'App\Entity\ProjectChild',
			'entityId' => 1,
			'historyContent' => 'Test history content',
			'banner' => 'Test project content',
			'icon' => 'Test thumbnail',
			'startDate' => new \DateTime('2021-01-01'),
			'city' => 'Test City',
			'latitude' => 0,
			'longitude' => 0,
			'historyExcerpt' => 'Test history excerpt'
		];
		$storyTelling = $this->createAndPersistSubProject($data);

		// Get the ID of the newly created StoryTelling
		$subProjectId = $storyTelling->getId();

		// Send the POST request to update the StoryTelling
		$updatedData = [
			'name' => 'Updated StoryTelling',
			'city' => 'Updated City',
			'historyExcerpt' => 'Updated history excerpt',
			'historyContent' => 'Updated history content',
			'longitude' => 10,
			'latitude' => 20,
			'startDate' => '01/01/2022'
		];
		$this->client->request('POST', '/admin/project-parent/1/subprojects/' . $subProjectId . '/edit', $updatedData);

		// Verify the response
		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertJson($response->getContent());
		$this->assertJsonStringEqualsJsonString(
			json_encode(['success' => true]),
			$response->getContent()
		);

		// Verify the StoryTelling has been updated in the database
		$updatedSubProject = $this->entityManager->getRepository(StoryTelling::class)->find($subProjectId);
		$this->assertEquals('Updated StoryTelling', $updatedSubProject->getName());
		$this->assertEquals('Updated City', $updatedSubProject->getCity());
		$this->assertEquals('Updated history excerpt', $updatedSubProject->getHistoryExcerpt());
		$this->assertEquals('Updated history content', $updatedSubProject->getHistoryContent());
		$this->assertEquals(10, $updatedSubProject->getLongitude());
		$this->assertEquals(20, $updatedSubProject->getLatitude());

		// Ensure the startDate is set to midnight
		$expectedDate = new \DateTime('2022-01-01 00:00:00');
		$actualDate = $updatedSubProject->getStartDate();
		$actualDate->setTime(0, 0, 0); // Set the time to midnight
		$this->assertEquals($expectedDate, $actualDate);
	}

	public function testEditGet(): void {
		// Create a new StoryTelling entity
		$data = [
			'name' => 'Test StoryTelling',
			'entityType' => 'App\Entity\ProjectChild',
			'entityId' => 1,
			'historyContent' => 'Test history content',
			'banner' => 'Test project content',
			'icon' => 'Test thumbnail',
			'startDate' => new \DateTime('2021-01-01'),
			'city' => 'Test City',
			'latitude' => 0,
			'longitude' => 0,
			'historyExcerpt' => 'Test history excerpt'
		];
		$storyTelling = $this->createAndPersistSubProject($data);

		// Get the ID of the newly created StoryTelling
		$subProjectId = $storyTelling->getId();

		// Send the GET request to edit the StoryTelling
		$this->client->request('GET', '/admin/project-child/1/subprojects/' . $subProjectId . '/edit');

		// Verify the response
		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertStringContainsString('Test history excerpt', $response->getContent());
		$this->assertStringContainsString('Test StoryTelling', $response->getContent());
		$this->assertStringContainsString('Test history content', $response->getContent());
	}

	public function testDeleteSubProject(): void {
		// Create a new StoryTelling entity
		$data = [
			'name' => 'Test StoryTelling',
			'entityType' => 'App\Entity\ProjectChild',
			'entityId' => 1,
			'historyContent' => 'Test history content',
			'banner' => 'Test project content',
			'icon' => 'Test thumbnail',
			'startDate' => new \DateTime('2021-01-01'),
			'city' => 'Test City',
			'latitude' => 0,
			'longitude' => 0,
			'historyExcerpt' => 'Test history excerpt'
		];
		$storyTelling = $this->createAndPersistSubProject($data);
		$subProjectId = $storyTelling->getId();

		// Send the DELETE request
		$this->client->request('DELETE', '/admin/project-parent/1/subprojects/' . $subProjectId.'/delete');

		// Verify the response
		$response = $this->client->getResponse();

		$this->assertJson($response->getContent());

		$this->assertJsonStringEqualsJsonString(
			json_encode(['success' => true]),
			$response->getContent()
		);

		// Verify the entity has been deleted
		$deletedSubProject = $this->entityManager->getRepository(StoryTelling::class)->find($subProjectId);
		$this->assertNull($deletedSubProject);
	}

	public function testDeleteNonExistentSubProject(): void {


		// Attempt to delete a non-existent StoryTelling
		$nonExistentSubProjectId = 9999; // Assuming this ID does not exist in the database

		// Send the DELETE request
		$this->client->request('DELETE', '/admin/project-parent/1/subprojects/' . $nonExistentSubProjectId . '/delete');

		// Verify the response
		$response = $this->client->getResponse();

		// Check if the response is JSON
		$this->assertJson($response->getContent());

		// Verify the response content
		$this->assertJsonStringEqualsJsonString(
			json_encode(['success' => false, 'message' => 'StoryTelling not found']),
			$response->getContent()
		);

		// Verify the status code
		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
	}
}
