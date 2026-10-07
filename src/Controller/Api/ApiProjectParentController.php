<?php

namespace App\Controller\Api;

use App\Repository\ProjectChildRepository;
use App\Repository\ProjectParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\GetResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1/projects/parents' )]
class ApiProjectParentController extends AbstractController {
	private ProjectParentRepository $projectParentService;
	private GetResponse $responseManager;

	public function __construct(
        ProjectParentRepository $projectParentService,
        GetResponse $responseManager)
    {
		$this->projectParentService = $projectParentService;
		$this->responseManager      = $responseManager;
	}

	#[Route( '/partner/{uuid}', name: 'api_project_parent_list', methods: [ 'GET' ] )]
	public function index( Request $request, string $uuid, ProjectChildRepository $childRepository ): Response {
		$params  = $request->query->all();
		$type_id = array_key_exists( 'typeId', $params ) && isset( $params['typeId'] ) ? intval( $params['typeId'] ) : 0;
        $data    = $childRepository->findProjectParentByPartner( $uuid, $type_id );
		$results = $data ?? [];

		return $this->responseManager->response( [
			'status' => 'success',
			'data'   => $results
		], Response::HTTP_OK );
	}

	#[Route( '/{id}', name: 'api_project_parent_show', methods: [ 'GET' ] )]
	public function show( int $id, SerializerInterface $serializer ): Response {
		$project_parent = $this->projectParentService->fetchProjectParentById( $id );

		if ( ! $project_parent ) {
			$response = [
				'status' => 'failed',
				'data'   => [ 'error' => 'Project parent not found' ]
			];

			return $this->responseManager->response( $response, Response::HTTP_NOT_FOUND );
		}

		$response = [
			'status' => 'success',
			'data'   => $project_parent
		];

		$json = $serializer->serialize(
			$response,
			'json',
			[
				'groups' =>
					[
						'project_parent',
						'project_type',
						'project_status',
						'localisation',
						'region',
						'city',
						'country',
						'gallery',
						'sub_project'
					]
			]
		);

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}
}
