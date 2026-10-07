<?php

namespace App\Controller\Api;


use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\StoryTelling;
use App\Entity\SubProject;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\ProjectParentRepository;
use App\Repository\StoryTellingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\GetResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1' )]
class ApiStoryTellingController extends AbstractController {
	private StoryTellingRepository $storyTellingRepository;
	private GetResponse $responseManager;
	private GalleryRepository $galleryRepository;



	public function __construct( GetResponse $responseManager, StoryTellingRepository $storyTellingRepository, GalleryRepository $galleryRepository ) {
		$this->responseManager     = $responseManager;
		$this->storyTellingRepository = $storyTellingRepository;
		$this->galleryRepository    = $galleryRepository;
	}

	#[Route( '/storytelling', name: 'api_story_telling_list', methods: [ 'GET' ] )]
	public function index( SerializerInterface $serializer ): Response {
		$storyTelling = $this->storyTellingRepository->findAll();
		$json         = $serializer->serialize(
			$storyTelling,
			'json',
			[ 'groups' => [ 'story_telling' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}

	#[Route( '/storytelling/{id}', name: 'api_story_telling_show', methods: [ 'GET' ] )]
	public function show( int $id, SerializerInterface $serializer ): Response {
		$storyTelling = $this->storyTellingRepository->find( $id );

		if ( ! $storyTelling ) {
			$response = [
				'status' => 'failed',
				'data'   => [ 'error' => 'story telling not found' ]
			];

			return $this->responseManager->response( $response, Response::HTTP_NOT_FOUND );
		}

		$galleries = $this->galleryRepository->findBy( [
			'entityType' => StoryTelling::class,
			'entityId'   => $storyTelling->getId()
		] );

		$data     = [
			'storyTelling' => $storyTelling,
			'galleries'    => $galleries
		];
		$response = [
			'status' => 'success',
			'data'   => $data
		];

		$json = $serializer->serialize(
			$response,
			'json',
			[ 'groups' => [ 'story_telling', 'gallery' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );

	}

}
