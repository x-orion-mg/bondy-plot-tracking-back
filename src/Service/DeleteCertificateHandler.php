<?php

namespace App\Service;

use App\Entity\Certificate;

class DeleteCertificateHandler extends AbstractDeleteHandler {
	protected function findEntity(int $id) {
		return $this->entityManager->getRepository(Certificate::class)->find($id);
	}

	protected function belongsToUser($entity, int $userId): bool {
		return $entity->getUser()->getId() === $userId;
	}

	protected function getFilePath($entity): string {
		return $entity->getFilePath();
	}
}
