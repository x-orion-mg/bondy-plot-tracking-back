<?php

namespace App\Tests\Controller\Admin;

use App\Entity\TreeSpecies;
use App\Entity\UsageType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TreeSpeciesControllerTest extends WebTestCase
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

    private function createAndPersistTreeSpecies(array $data): TreeSpecies
    {
        $treeSpecies = new TreeSpecies();
        $treeSpecies->setCommonName($data['commonName'] ?? 'Test Common Name');
        $treeSpecies->setScientificName($data['scientificName'] ?? 'Test Scientific Name');
        $treeSpecies->setTrackCode($data['trackCode'] ?? 'TEST123');
        $treeSpecies->setBanner($data['banner'] ?? 'https://example.com/banner.jpg');
        $treeSpecies->setIcon($data['icon'] ?? 'https://example.com/icon.png');

        // Associer un UsageType existant ou en créer un nouveau
        $usageType = $this->entityManager->getRepository(UsageType::class)->findOneBy([]);
        if (!$usageType) {
            $usageType = new UsageType();
            $usageType->setLabel('Test Usage Type');
            $this->entityManager->persist($usageType);
            $this->entityManager->flush();
        }
        $treeSpecies->setUsageType($usageType);

        $this->entityManager->persist($treeSpecies);
        $this->entityManager->flush();

        return $treeSpecies;
    }

    public function testNewGet(): void
    {
        // Envoi d'une requête GET à la route de création d'une nouvelle espèce d'arbre
        $this->client->request('GET', '/admin/tree-species/new');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Nom commun', $response->getContent());
    }

    public function testNewPost(): void
    {
        // Données pour la nouvelle espèce d'arbre
        $data = [
            'commonName' => 'New Common Name',
            'scientificName' => 'New Scientific Name',
            'trackCode' => 'NEW123',
            'banner' => 'https://example.com/new-banner.jpg',
            'icon' => 'https://example.com/new-icon.png',
        ];

        // Envoi d'une requête POST pour créer une nouvelle espèce d'arbre
        $this->client->request('POST', '/admin/tree-species/new', $data);

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        // Vérification que l'espèce d'arbre a été créée dans la base de données
        $treeSpecies = $this->entityManager->getRepository(TreeSpecies::class)->findOneBy(['commonName' => 'New Common Name']);
        $this->assertNotNull($treeSpecies);
        $this->assertEquals('New Scientific Name', $treeSpecies->getScientificName());
        $this->assertEquals('NEW123', $treeSpecies->getTrackCode());
        $this->assertEquals('https://example.com/new-banner.jpg', $treeSpecies->getBanner());
        $this->assertEquals('https://example.com/new-icon.png', $treeSpecies->getIcon());
    }

    public function testEdit(): void
    {
        // Création d'une nouvelle espèce d'arbre
        $data = [
            'commonName' => 'Test Common Name',
            'scientificName' => 'Test Scientific Name',
            'trackCode' => 'TEST123',
            'banner' => 'https://example.com/banner.jpg',
            'icon' => 'https://example.com/icon.png',
        ];
        $treeSpecies = $this->createAndPersistTreeSpecies($data);

        // Récupération de l'ID de l'espèce d'arbre créée
        $treeSpeciesId = $treeSpecies->getId();

        // Données mises à jour pour l'espèce d'arbre
        $updatedData = [
            'commonName' => 'Updated Common Name',
            'scientificName' => 'Updated Scientific Name',
            'trackCode' => 'UPDATED123',
            'banner' => 'https://example.com/updated-banner.jpg',
            'icon' => 'https://example.com/updated-icon.png',
        ];

        // Envoi d'une requête POST pour mettre à jour l'espèce d'arbre
        $this->client->request('POST', '/admin/tree-species/' . $treeSpeciesId . '/edit', $updatedData);

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => true]),
            $response->getContent()
        );

        // Vérification que l'espèce d'arbre a été mise à jour dans la base de données
        $updatedTreeSpecies = $this->entityManager->getRepository(TreeSpecies::class)->find($treeSpeciesId);
        $this->assertEquals('Updated Common Name', $updatedTreeSpecies->getCommonName());
        $this->assertEquals('Updated Scientific Name', $updatedTreeSpecies->getScientificName());
        $this->assertEquals('UPDATED123', $updatedTreeSpecies->getTrackCode());
        $this->assertEquals('https://example.com/updated-banner.jpg', $updatedTreeSpecies->getBanner());
        $this->assertEquals('https://example.com/updated-icon.png', $updatedTreeSpecies->getIcon());
    }

    public function testEditGet(): void
    {
        // Création d'une nouvelle espèce d'arbre
        $data = [
            'commonName' => 'Test Common Name',
            'scientificName' => 'Test Scientific Name',
            'trackCode' => 'TEST123',
            'banner' => 'https://example.com/banner.jpg',
            'icon' => 'https://example.com/icon.png',
        ];
        $treeSpecies = $this->createAndPersistTreeSpecies($data);

        // Récupération de l'ID de l'espèce d'arbre créée
        $treeSpeciesId = $treeSpecies->getId();

        // Envoi d'une requête GET pour afficher le formulaire d'édition
        $this->client->request('GET', '/admin/tree-species/' . $treeSpeciesId . '/edit');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Test Common Name', $response->getContent());
        $this->assertStringContainsString('Test Scientific Name', $response->getContent());
    }

    public function testDeleteTreeSpecies(): void
    {
        // Création d'une nouvelle espèce d'arbre
        $data = [
            'commonName' => 'Test Common Name',
            'scientificName' => 'Test Scientific Name',
            'trackCode' => 'TEST123',
            'banner' => 'https://example.com/banner.jpg',
            'icon' => 'https://example.com/icon.png',
        ];
        $treeSpecies = $this->createAndPersistTreeSpecies($data);
        $treeSpeciesId = $treeSpecies->getId();

        // Envoi d'une requête DELETE pour supprimer l'espèce d'arbre
        $this->client->request('DELETE', '/admin/tree-species/' . $treeSpeciesId . '/delete');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => true]),
            $response->getContent()
        );

        // Vérification que l'espèce d'arbre a été supprimée de la base de données
        $deletedTreeSpecies = $this->entityManager->getRepository(TreeSpecies::class)->find($treeSpeciesId);
        $this->assertNull($deletedTreeSpecies);
    }

    public function testDeleteNonExistentTreeSpecies(): void
    {
        // Tentative de suppression d'une espèce d'arbre inexistante
        $nonExistentTreeSpeciesId = 9999; // Supposons que cet ID n'existe pas dans la base de données

        // Envoi d'une requête DELETE
        $this->client->request('DELETE', '/admin/tree-species/' . $nonExistentTreeSpeciesId . '/delete');

        // Vérification de la réponse
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['success' => false, 'message' => 'TreeSpecies not found']),
            $response->getContent()
        );

        // Vérification du code de statut
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}