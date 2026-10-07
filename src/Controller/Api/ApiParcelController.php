<?php

namespace App\Controller\Api;

use App\Entity\AllocatedTree;
use App\Entity\Gallery;
use App\Entity\Parcel;
use App\Entity\ProjectChild;
use App\Entity\StoryTelling;
use App\Entity\TreeSpecies;
use App\Entity\User;
use App\Repository\GalleryRepository;
use App\Repository\ParcelRepository;
use App\Repository\ProjectParentRepository;
use App\Repository\TreeSpeciesRepository;
use App\Service\TreeSpeciesService;
use App\Utils\GetResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\TreeService;

#[Route( '/api/v1/projects' )]
class ApiParcelController extends AbstractApiController {
	private GetResponse $responseManager;
	private ParcelRepository $parcelRepository;
	private EntityManagerInterface $entityManager;
	private const PARCEL_NOT_FOUND = 'Parcel not found';
	private const PROJECT_CHILD_NOT_FOUND = 'Project child not found';
	private const PARTNER_NOT_FOUND = 'Partner not found';
	private TreeService $treeService;

	public function __construct(
		GetResponse $responseManager,
		ParcelRepository $parcelRepository,
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		TreeService $treeService
	) {
		parent::__construct( $serializer );
		$this->responseManager       = $responseManager;
		$this->entityManager         = $entityManager;
		$this->parcelRepository      = $parcelRepository;
		$this->treeService = $treeService;
	}

	#[Route( '/parcel', name: 'api_parcel_list', methods: [ 'GET' ] )]
	public function index( SerializerInterface $serializer ): Response {
		$parcels = $this->parcelRepository->findAll();
		if ( ! $parcels ) {
			return $this->createErrorResponse( self::PARCEL_NOT_FOUND, Response::HTTP_NOT_FOUND );
		}

		return $this->createSuccessResponse( [ 'parcels' => $parcels ], [ 'parcel' ] );
	}

	#[Route( '/parcel/{id}', name: 'api_parcel_show', methods: [ 'GET' ] )]
	public function show( int $id, SerializerInterface $serializer ): Response {
		$parcel = $this->parcelRepository->find( $id );
		if ( ! $parcel ) {
			return $this->createErrorResponse( self::PARCEL_NOT_FOUND, Response::HTTP_NOT_FOUND );
		}

		return $this->createSuccessResponse( [ 'parcel' => $parcel ], [ 'parcel' ] );
	}

	private function findProjectChildAndParcel( int $parentId, int $partnerId, int $childId, int $parcelId = null ): ?array {
		$projectChild = $this->entityManager->getRepository( ProjectChild::class )->findOneBy( [
			'projectParent' => $parentId,
			'user'       => $partnerId,
			'id'            => $childId
		] );

		if ( ! $projectChild ) {
			return [ 'error' => self::PROJECT_CHILD_NOT_FOUND, 'status' => Response::HTTP_NOT_FOUND ];
		}

		if ( $parcelId !== null ) {
			$parcel = $this->parcelRepository->findParcelWithProjectChild( $parcelId, $parentId, $partnerId, $childId );
			if ( ! $parcel ) {
				return [ 'error' => self::PARCEL_NOT_FOUND, 'status' => Response::HTTP_NOT_FOUND ];
			}
		} else {
			$parcel = null;
		}

		return [ 'projectChild' => $projectChild, 'parcel' => $parcel ];
	}

	#[Route( '/parents/{parentId}/partners/{partnerId}/child/{childId}/parcel', name: 'api_project_child_parcels', methods: [ 'GET' ] )]
	public function getProjectChildParcels( int $parentId, int $partnerId, int $childId, TreeSpeciesService $treeSpeciesService ): Response {
		$result = $this->findProjectChildAndParcel( $parentId, $partnerId, $childId );

		if ( isset( $result['error'] ) ) {
			return $this->responseManager->response( [
				'status' => 'failed',
				'data'   => [ 'error' => $result['error'] ]
			], $result['status'] );
		}

		$projectChild = $result['projectChild'];
		$parcels      = $this->parcelRepository->findBy( [ 'projectChild' => $projectChild ] );

		$treeSpecies     = $treeSpeciesService->aggregateTreeSpecies( $parcels );
		$treeSpeciesData = $treeSpeciesService->getTreeSpeciesData( $treeSpecies );

		$data = [
			'projectChild' => $projectChild,
			'parcels'      => $parcels,
			'treeSpecies'  => $treeSpeciesData
		];

		return $this->createSuccessResponse( $data, [
			'parcel_list',
			'parcel_tree_species',
			'tree_species',
			'story_telling_tree_species'
		] );
	}

	#[Route( '/parents/{parentId}/partners/{partnerId}/child/{childId}/parcel/{parcelId}', name: 'api_project_child_parcel', methods: [ 'GET' ] )]
	public function getProjectChildParcel( int $parentId, int $partnerId, int $childId, int $parcelId, SerializerInterface $serializer, ProjectParentRepository $projectParentRepository, TreeSpeciesService $treeSpeciesService ): Response {
		$result = $this->findProjectChildAndParcel( $parentId, $partnerId, $childId, $parcelId );

		if ( isset( $result['error'] ) ) {
			return $this->responseManager->response( [
				'status' => 'failed',
				'data'   => [ 'error' => $result['error'] ]
			], $result['status'] );
		}

		$parcel          = $result['parcel'];
		$treeSpecies     = $treeSpeciesService->aggregateTreeSpecies( [ $parcel ] );
		$treeSpeciesData = $treeSpeciesService->getTreeSpeciesData( $treeSpecies );

		$galleries = $this->entityManager->getRepository( Gallery::class )->findBy( [
			'entityType' => Parcel::class,
			'entityId'   => $parcel->getId()
		] );

		$storyTelling = $this->entityManager->getRepository( StoryTelling::class )->findBy( [
			'entityType' => Parcel::class,
			'entityID'   => $parcel->getId()
		] );

		$projectParent = $projectParentRepository->find( $parentId );
		$localisation  = $projectParent ? $projectParent->getLocalisation() : null;

		$data = [
			'projectChild' => $result['projectChild'],
			'parcel'       => $parcel,
			'treeSpecies'  => $treeSpeciesData,
			'galleries'    => $galleries,
			'storyTelling' => $storyTelling,
			'localisation' => $localisation
		];

		return $this->createSuccessResponse( $data, [
			'parcel_show',
			'tree_species',
			'usage_type',
			'gallery',
			'story_telling',
			'localisation'
		] );
	}

	#[Route( '/parents/{parentId}/partners/{partnerId}/child/{childId}/parcel/{parcelId}/trees', name: 'api_project_child_parcel_tree_species', methods: [ 'GET' ] )]
	public function getParcelTrees( int $parentId, int $partnerId, int $childId, int $parcelId, SerializerInterface $serializer ): Response {
		$result = $this->findProjectChildAndParcel( $parentId, $partnerId, $childId, $parcelId );

		if ( isset( $result['error'] ) ) {
			return $this->responseManager->response( [
				'status' => 'failed',
				'data'   => [ 'error' => $result['error'] ]
			], $result['status'] );
		}

		$data = [ 'parcel' => $result['parcel'] ];

		return $this->createSuccessResponse( $data, [ 'parcel_trees' ] );
	}

	#[Route( '/parents/{parentId}/partners/{partnerId}/child/{childId}/parcel/{parcelId}/trees/{trackCode}', name: 'api_project_child_parcel_tree', methods: [ 'GET' ] )]
	public function getTree(int $parentId, int $partnerId, int $childId, int $parcelId, string $trackCode): Response {
		$result = $this->treeService->getTreeData($parentId, $partnerId, $childId, $parcelId, $trackCode);

		if (isset($result['error'])) {
			return $this->createErrorResponse($result['error'], $result['status']);
		}

		return $this->createSuccessResponse($result, ['parcel_tree', 'user', 'allocated_tree_user']);
	}

	private function findMatchedTree( array $treesJson, string $trackCode ): ?array {
		foreach ( $treesJson as $tree ) {
			if ( isset( $tree['id'] ) && $tree['id'] === $trackCode ) {
				return $tree;
			}
		}

		return null;
	}
}
