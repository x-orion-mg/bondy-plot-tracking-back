<?php

namespace App\Controller\Api;

use App\Service\DownloadRequestHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route( '/api/v1' )]
class ApiDownloadController extends AbstractApiController {
	private DownloadRequestHandler $downloadRequestHandler;

	public function __construct( DownloadRequestHandler $downloadRequestHandler, SerializerInterface $serializer, ) {
		parent::__construct( $serializer );
		$this->downloadRequestHandler = $downloadRequestHandler;
	}

	#[Route( '/download', name: 'api_download', methods: [ 'POST' ] )]
	public function download( Request $request ): JsonResponse {
		$data = json_decode($request->getContent(), true);

		$uuid = $data['uuid'] ?? null;
		$code = $data['code'] ?? null;
		$type = $data['type'] ?? null;

		try {
			$fileUrl = $this->downloadRequestHandler->handleDownloadRequest( $uuid, $code, $type );

			return $this->createSuccessResponse( [ 'url' => $fileUrl ], [] );
		} catch ( \InvalidArgumentException $e ) {
			return $this->createErrorResponse( $e->getMessage(), Response::HTTP_UNAUTHORIZED );
		} catch ( \Exception $e ) {
			return $this->createErrorResponse( $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );
		}
	}
}
