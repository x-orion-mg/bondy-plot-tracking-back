<?php

namespace App\Controller\Api;

use App\Entity\AllocatedTree;
use App\Entity\User;
use App\Service\TreeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1/users' )]
class ApiUserController extends AbstractApiController {
	private TreeService $treeService;
	private EntityManagerInterface $entityManager;

	public function __construct( TreeService $treeService, EntityManagerInterface $entityManager, SerializerInterface $serializer ) {
		parent::__construct( $serializer );
		$this->treeService   = $treeService;
		$this->entityManager = $entityManager;
	}

	#[Route( '/{userUuid}/tree/{trackCode}', name: 'api_user_tree', methods: [ 'GET' ] )]
	public function getTreeByUser( string $userUuid, string $trackCode ): Response {
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'uuid' => $userUuid ] );

		if ( ! $user ) {
			return $this->createErrorResponse( 'User not found', Response::HTTP_NOT_FOUND );
		}

		$allocatedTree = $this->entityManager->getRepository( AllocatedTree::class )->findOneBy( [
			'user'      => $user,
			'trackCode' => $trackCode
		] );

		if ( ! $allocatedTree ) {
			return $this->createErrorResponse( 'Allocated tree not found for this user', Response::HTTP_NOT_FOUND );
		}

		$result = $this->treeService->getTreeData(
			$allocatedTree->getParcel()->getProjectChild()->getProjectParent()->getId(), $allocatedTree->getParcel()->getProjectChild()->getUser()->getId(),
			$allocatedTree->getParcel()->getProjectChild()->getId(),
			$allocatedTree->getParcel()->getId(), $trackCode );

		if ( isset( $result['error'] ) ) {
			return $this->createErrorResponse( $result['error'], $result['status'] );
		}

		return $this->createSuccessResponse( $result, [ 'parcel_tree', 'user', 'allocated_tree_user' ] );
	}

	#[Route( '/uuid', name: 'api_user_uuid_by_email', methods: [ 'GET' ] )]
	public function getUuidByEmail( Request $request ): Response {
		$email = $request->query->get( 'email' );
		if ( ! $email ) {
			return $this->createErrorResponse( 'Email is required', Response::HTTP_UNAUTHORIZED );
		}
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'email' => $email ] );

		if ( ! $user ) {
			return $this->createErrorResponse( 'User not found', Response::HTTP_NOT_FOUND );
		}

		return $this->createSuccessResponse( [ 'uuid' => $user->getUuid() ], [] );
	}

	/*#[Route( '/check-token', name: 'api_user_check_token', methods: [ 'GET' ] )]
	public function checkToken( Request $request ): Response {
		$token = $request->query->get( 'token' );
		if ( ! $token ) {
			return $this->createErrorResponse( 'Token is required', Response::HTTP_UNAUTHORIZED );
		}
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'uuid' => $token ] );
	}*/

}
