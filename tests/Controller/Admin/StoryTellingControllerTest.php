<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\SubProjectController;
use App\Entity\Profile;
use App\Entity\SubProject;
use App\Entity\User;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\SubProjectRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Asset\Packages;

class StoryTellingControllerTest extends WebTestCase {
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

	private function createAndPersistSubProject(array $data): SubProject {
		$subProject = new SubProject();
		$subProject->setName($data['name'] ?? 'Test SubProject');
		$subProject->setEntityType($data['entityType'] ?? 'App\Entity\ProjectChild');
		$subProject->setEntityId($data['entityId'] ?? 1);
		$subProject->setHistoryContent($data['historyContent'] ?? 'Test history content');
		$subProject->setBanner($data['banner'] ?? 'Test project content');
		$subProject->setIcon($data['icon'] ?? 'Test thumbnail');
		$subProject->setStartDate($data['startDate'] ?? new \DateTime('2021-01-01'));
		$subProject->setCity($data['city'] ?? 'Test City');
		$subProject->setLatitude($data['latitude'] ?? 0);
		$subProject->setLongitude($data['longitude'] ?? 0);
		$subProject->setHistoryExcerpt($data['historyExcerpt'] ?? 'Test history excerpt');

		$this->entityManager->persist($subProject);
		$this->entityManager->flush();

		return $subProject;
	}

	public function testNewGet(): void {
		// Send the GET request to the new SubProject route
		$this->client->request('GET', '/admin/project-child/1/subprojects/new');

		// Verify the response
		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertStringContainsString('Nom du sous-projet', $response->getContent());
	}

	public function testNewPost(): void {
		// Data for the new SubProject
		$data = [
			'name' => 'New SubProject',
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

		$subProject = $this->entityManager->getRepository(SubProject::class)->findOneBy(['name' => 'New SubProject']);
		$this->assertNotNull($subProject);
		$this->assertEquals('New City', $subProject->getCity());
		$this->assertEquals('New history excerpt', $subProject->getHistoryExcerpt());
		$this->assertEquals('New history content', $subProject->getHistoryContent());
		$this->assertEquals(10, $subProject->getLongitude());
		$this->assertEquals(20, $subProject->getLatitude());

		// Ensure the startDate is set to the expected date
		$expectedDate = new \DateTime('2022-01-01 00:00:00');
		$actualDate = $subProject->getStartDate();
		$actualDate->setTime(0, 0, 0); // Set the time to midnight
		$this->assertEquals($expectedDate, $actualDate);
	}

	public function testEdit(): void {
		// Create a new SubProject entity
		$data = [
			'name' => 'Test SubProject',
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
		$subProject = $this->createAndPersistSubProject($data);

		// Get the ID of the newly created SubProject
		$subProjectId = $subProject->getId();

		// Send the POST request to update the SubProject
		$updatedData = [
			'name' => 'Updated SubProject',
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

		// Verify the SubProject has been updated in the database
		$updatedSubProject = $this->entityManager->getRepository(SubProject::class)->find($subProjectId);
		$this->assertEquals('Updated SubProject', $updatedSubProject->getName());
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
		// Create a new SubProject entity
		$data = [
			'name' => 'Test SubProject',
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
		$subProject = $this->createAndPersistSubProject($data);

		// Get the ID of the newly created SubProject
		$subProjectId = $subProject->getId();

		// Send the GET request to edit the SubProject
		$this->client->request('GET', '/admin/project-child/1/subprojects/' . $subProjectId . '/edit');

		// Verify the response
		$response = $this->client->getResponse();
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertStringContainsString('Test history excerpt', $response->getContent());
		$this->assertStringContainsString('Test SubProject', $response->getContent());
		$this->assertStringContainsString('Test history content', $response->getContent());
	}

	public function testDeleteSubProject(): void {
		// Create a new SubProject entity
		$data = [
			'name' => 'Test SubProject',
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
		$subProject = $this->createAndPersistSubProject($data);
		$subProjectId = $subProject->getId();

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
		$deletedSubProject = $this->entityManager->getRepository(SubProject::class)->find($subProjectId);
		$this->assertNull($deletedSubProject);
	}

	public function testDeleteNonExistentSubProject(): void {


		// Attempt to delete a non-existent SubProject
		$nonExistentSubProjectId = 9999; // Assuming this ID does not exist in the database

		// Send the DELETE request
		$this->client->request('DELETE', '/admin/project-parent/1/subprojects/' . $nonExistentSubProjectId . '/delete');

		// Verify the response
		$response = $this->client->getResponse();

		// Check if the response is JSON
		$this->assertJson($response->getContent());

		// Verify the response content
		$this->assertJsonStringEqualsJsonString(
			json_encode(['success' => false, 'message' => 'SubProject not found']),
			$response->getContent()
		);

		// Verify the status code
		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
	}
}
