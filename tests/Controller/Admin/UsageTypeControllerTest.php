<?php

namespace App\Tests\Controller\Admin;

use App\Entity\UsageType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UsageTypeControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Connexion d'un utilisateur de test
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);
    }

    private function createAndPersistUsageType(array $data): UsageType
    {
        $usageType = new UsageType();
        $usageType->setLabel($data['label'] ?? 'Test Usage Type');
        $usageType->setIcon($data['icon'] ?? 'https://example.com/icon.png');

        $this->entityManager->persist($usageType);
        $this->entityManager->flush();

        return $usageType;
    }

    public function testNewGet(): void
    {
        // Envoi d'une requête GET à la route de création d'un nouveau type d'usage
        $this->client->request('GET', '/admin/usage-type/new');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Label', $response->getContent());
    }

    public function testNewPost(): void
    {
        // Données pour le nouveau type d'usage
        $data = [
            'label' => 'New Usage Type',
            'icon' => 'https://example.com/new-icon.png',
        ];

        // Envoi d'une requête POST pour créer un nouveau type d'usage
        $this->client->request('POST', '/admin/usage-type/new', $data);

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        // Vérification que le type d'usage a été créé dans la base de données
        $usageType = $this->entityManager->getRepository(UsageType::class)->findOneBy(['label' => 'New Usage Type']);
        $this->assertNotNull($usageType);
        $this->assertEquals('https://example.com/new-icon.png', $usageType->getIcon());
    }

    public function testEdit(): void
    {
        // Création d'un nouveau type d'usage
        $data = [
            'label' => 'Test Usage Type',
            'icon' => 'https://example.com/icon.png',
        ];
        $usageType = $this->createAndPersistUsageType($data);

        // Récupération de l'ID du type d'usage créé
        $usageTypeId = $usageType->getId();

        // Données mises à jour pour le type d'usage
        $updatedData = [
            'label' => 'Updated Usage Type',
            'icon' => 'https://example.com/updated-icon.png',
        ];

        // Envoi d'une requête POST pour mettre à jour le type d'usage
        $this->client->request('POST', '/admin/usage-type/' . $usageTypeId . '/edit', $updatedData);

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => true]),
            $response->getContent()
        );

        // Vérification que le type d'usage a été mis à jour dans la base de données
        $updatedUsageType = $this->entityManager->getRepository(UsageType::class)->find($usageTypeId);
        $this->assertEquals('Updated Usage Type', $updatedUsageType->getLabel());
        $this->assertEquals('https://example.com/updated-icon.png', $updatedUsageType->getIcon());
    }

    public function testEditGet(): void
    {
        // Création d'un nouveau type d'usage
        $data = [
            'label' => 'Test Usage Type',
            'icon' => 'https://example.com/icon.png',
        ];
        $usageType = $this->createAndPersistUsageType($data);

        // Récupération de l'ID du type d'usage créé
        $usageTypeId = $usageType->getId();

        // Envoi d'une requête GET pour afficher le formulaire d'édition
        $this->client->request('GET', '/admin/usage-type/' . $usageTypeId . '/edit');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Test Usage Type', $response->getContent());
        $this->assertStringContainsString('https://example.com/icon.png', $response->getContent());
    }

    public function testDeleteUsageType(): void
    {
        // Création d'un nouveau type d'usage
        $data = [
            'label' => 'Test Usage Type',
            'icon' => 'https://example.com/icon.png',
        ];
        $usageType = $this->createAndPersistUsageType($data);
        $usageTypeId = $usageType->getId();

        // Envoi d'une requête DELETE pour supprimer le type d'usage
        $this->client->request('DELETE', '/admin/usage-type/' . $usageTypeId . '/delete');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => true]),
            $response->getContent()
        );

        // Vérification que le type d'usage a été supprimé de la base de données
        $deletedUsageType = $this->entityManager->getRepository(UsageType::class)->find($usageTypeId);
        $this->assertNull($deletedUsageType);
    }

    public function testDeleteNonExistentUsageType(): void
    {
        // Tentative de suppression d'un type d'usage inexistant
        $nonExistentUsageTypeId = 9999; // Supposons que cet ID n'existe pas dans la base de données

        // Envoi d'une requête DELETE
        $this->client->request('DELETE', '/admin/usage-type/' . $nonExistentUsageTypeId . '/delete');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => false, 'message' => 'UsageType not found']),
            $response->getContent()
        );

        // Vérification du code de statut
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}