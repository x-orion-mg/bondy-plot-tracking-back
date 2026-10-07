<?php

namespace App\Controller\Api;


use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\SubProject;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\ProjectParentRepository;
use App\Repository\StoryTellingRepository;
use App\Repository\SubProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\GetResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1' )]
class ApiSubProjectController extends AbstractController {
	private SubProjectRepository $subProjectRepository;
	private GetResponse $responseManager;
	private GalleryRepository $galleryRepository;
	private ProjectParentRepository $projectParentRepository;
	private ProjectChildRepository $projectChildRepository;



	public function __construct( GetResponse $responseManager, SubProjectRepository $subProjectRepository, GalleryRepository $galleryRepository, ProjectChildRepository $projectChildRepository, ProjectParentRepository $projectParentRepository ) {
		$this->responseManager     = $responseManager;
		$this->subProjectRepository = $subProjectRepository;
		$this->galleryRepository    = $galleryRepository;
		$this->projectChildRepository = $projectChildRepository;
		$this->projectParentRepository = $projectParentRepository;
	}

	#[Route( '/subprojects', name: 'api_subprojects_list', methods: [ 'GET' ] )]
	public function index( SerializerInterface $serializer ): Response {
		$projectChild = $this->subProjectRepository->findAll();
		$json         = $serializer->serialize(
			$projectChild,
			'json',
			[ 'groups' => [ 'sub_project' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}

	#[Route( '/subproject/{id}', name: 'api_subproject_show', methods: [ 'GET' ] )]
	public function show( int $id, SerializerInterface $serializer ): Response {
		$subProject = $this->subProjectRepository->find( $id );

		if ( ! $subProject ) {
			$response = [
				'status' => 'failed',
				'data'   => [ 'error' => 'subproject not found' ]
			];

			return $this->responseManager->response( $response, Response::HTTP_NOT_FOUND );
		}

		$galleries = $this->galleryRepository->findBy( [
			'entityType' => SubProject::class,
			'entityId'   => $subProject->getId()
		] );

		$localisation = null;
		if ($subProject->getEntityType() === ProjectChild::class) {
			$projectChild = $this->projectChildRepository->find($subProject->getEntityId());
			if ($projectChild) {
				$projectParent = $projectChild->getProjectParent();
				if ($projectParent) {
					$localisation = $projectParent->getLocalisation();
				}
			}
		} elseif ($subProject->getEntityType() === ProjectParent::class) {
			$projectParent = $this->projectParentRepository->find($subProject->getEntityId());
			if ($projectParent) {
				$localisation = $projectParent->getLocalisation();
			}
		}

		$data     = [
			'subProjects' => $subProject,
			'galleries'   => $galleries,
			'localisation' => $localisation
		];
		$response = [
			'status' => 'success',
			'data'   => $data
		];

		$json = $serializer->serialize(
			$response,
			'json',
			[ 'groups' => [ 'sub_project', 'gallery', 'localisation' ] ]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );

	}

}
