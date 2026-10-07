<?php

namespace App\Controller\Api;


use App\Entity\ProjectChild;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\StoryTellingRepository;
use App\Repository\SubProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\GetResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1/projects' )]
class ApiProjectChildController extends AbstractController {
	private ProjectChildRepository $projectChildRepository;
	private GetResponse $responseManager;
	private SubProjectRepository $subProjectRepository;
	private GalleryRepository $galleryRepository;
	private StoryTellingRepository $storyTellingRepository;

	public function __construct( ProjectChildRepository $projectChildRepository, GetResponse $responseManager, SubProjectRepository $subProjectRepository, GalleryRepository $galleryRepository, StoryTellingRepository $storyTellingRepository ) {
		$this->projectChildRepository = $projectChildRepository;
		$this->responseManager       = $responseManager;
		$this->subProjectRepository   = $subProjectRepository;
		$this->galleryRepository      = $galleryRepository;
		$this->storyTellingRepository = $storyTellingRepository;
	}

	#[Route( '/child', name: 'api_project_child_list', methods: [ 'GET' ] )]
	public function index( SerializerInterface $serializer ): Response {
		$projectChild = $this->projectChildRepository->findAll();
		$json         = $serializer->serialize(
			$projectChild,
			'json',
			[ 'groups' => [ 'project_child' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}

	#[Route( '/child/{id}', name: 'api_project_child_show', methods: [ 'GET' ] )]
	public function show( int $id, SerializerInterface $serializer ): Response {
		$projectChild = $this->projectChildRepository->find( $id );

		return $this->extracted( $projectChild, $serializer );

	}

	#[Route( '/parents/{parentId}/partners/{partnerId}/child', name: 'get_project_child_by_parent_and_partner', methods: [ 'GET' ] )]
	public function getProjectChildByParentAndPartner( int $parentId, int $partnerId, SerializerInterface $serializer ): Response {
		$projectChild = $this->projectChildRepository->findOneBy( [
			'projectParent' => $parentId,
			'user'       => $partnerId
		] );

		return $this->extracted( $projectChild, $serializer );
	}

	/**
	 * @param object|null $projectChild
	 * @param SerializerInterface $serializer
	 *
	 * @return JsonResponse
	 */
	public function extracted( ?object $projectChild, SerializerInterface $serializer ): JsonResponse {
		if ( ! $projectChild ) {
			$response = [
				'status' => 'failed',
				'data'   => [ 'error' => 'Project child not found' ]
			];

			return $this->responseManager->response( $response, Response::HTTP_NOT_FOUND );
		}

		$subProjects = $this->subProjectRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityId'   => $projectChild->getId()
		] );

		$galleries = $this->galleryRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityId'   => $projectChild->getId()
		] );

		$storyTelling = $this->storyTellingRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityID'   => $projectChild->getId()
		] );

		$data = [
			'projectChild' => $projectChild,
			'subProjects'  => $subProjects,
			'galleries'    => $galleries,
			'storyTelling' => $storyTelling
		];


		$response = [
			'status' => 'success',
			'data'   => $data
		];

		$json = $serializer->serialize(
			$response,
			'json',
			[ 'groups' => [ 'project_child', 'sub_project', 'gallery', 'story_telling', 'user' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}
}
