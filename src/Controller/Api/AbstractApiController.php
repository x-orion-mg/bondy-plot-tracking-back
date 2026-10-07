<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractApiController extends AbstractController {

	private SerializerInterface $serializer;

	public function __construct( SerializerInterface $serializer ) {
		$this->serializer = $serializer;
	}

	public function createSuccessResponse( array $data, array $groups ): JsonResponse {
		$data = [ 'status' => 'success', 'data' => $data ];
		$json = $this->serializer->serialize( $data, 'json', [ 'groups' => $groups ] );

		return new JsonResponse( $json, Response::HTTP_OK, [], true );
	}

	protected function createErrorResponse( string $error, int $statusCode ): JsonResponse {
		$response = [
			'status' => 'failed',
			'data'   => [ 'error' => $error ]
		];

		return new JsonResponse( $response, $statusCode );
	}
}
