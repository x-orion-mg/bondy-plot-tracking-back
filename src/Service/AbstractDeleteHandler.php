<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractDeleteHandler {
	protected EntityManagerInterface $entityManager;

	public function __construct( EntityManagerInterface $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function handleDelete( int $id, int $userId ): JsonResponse {
		$entity = $this->findEntity( $id );

		if ( $entity && $this->belongsToUser( $entity, $userId ) ) {
			$filePath = $this->getFilePath( $entity );
			if ( file_exists( $filePath ) ) {
				unlink( $filePath );
			}
			try {
				$this->entityManager->remove( $entity );
				$this->entityManager->flush();

				return new JsonResponse( [ 'success' => true ], Response::HTTP_OK );
			} catch ( \Exception $e ) {
				return new JsonResponse( [
					'success' => false,
					'message' => $e->getMessage()
				], Response::HTTP_INTERNAL_SERVER_ERROR );
			}
		}

		return new JsonResponse( [
			'success' => false,
			'message' => 'Entity not found or does not belong to the user.'
		], Response::HTTP_NOT_FOUND );
	}

	abstract protected function findEntity( int $id );

	abstract protected function belongsToUser( $entity, int $userId ): bool;

	abstract protected function getFilePath( $entity ): string;
}
