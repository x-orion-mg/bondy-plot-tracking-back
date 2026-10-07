<?php

namespace App\Service;

use App\Entity\ReportHistory;

class DeleteReportHistoryHandler extends AbstractDeleteHandler {
	protected function findEntity(int $id) {
		return $this->entityManager->getRepository(ReportHistory::class)->find($id);
	}

	protected function belongsToUser($entity, int $userId): bool {
		return $entity->getUser()->getId() === $userId;
	}

	protected function getFilePath($entity): string {
		return $entity->getFilePath();
	}
}
