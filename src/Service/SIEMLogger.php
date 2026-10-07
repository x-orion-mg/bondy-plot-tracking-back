<?php

namespace App\Service;

use App\Entity\SIEMEntry;
use App\Entity\User;
use App\Entity\ErrorType;
use App\Entity\EventType;
use App\Entity\ResourceType;
use Doctrine\ORM\EntityManagerInterface;

class SIEMLogger
{
	public function __construct(private readonly EntityManagerInterface $em) {}

	public function logEvent(
		?User $user,
		ErrorType $errorType,
		EventType $eventType,
		ResourceType $resourceType = null,
		int $eventResult = 1, // 1 = succès, 0 = échec
		string $sourceAddr = null,
		string $additionalInfo = '',
		int $targetSourceId = null
	): void {
		$siemEntry = new SIEMEntry();
		$siemEntry->setUser($user);
		$siemEntry->setErrorType($errorType);
		$siemEntry->setEventType($eventType);
		$siemEntry->setResourceType($resourceType);
		$siemEntry->setEventTime(new \DateTimeImmutable());
		$siemEntry->setEventResult($eventResult);
		$siemEntry->setSourceAddr($sourceAddr ?? $_SERVER['REMOTE_ADDR']);
		$siemEntry->setAdditionalInfo($additionalInfo);
		if ($targetSourceId !== null) {
			$siemEntry->setTargetSourceId($targetSourceId);
		}

		$this->em->persist($siemEntry);
		$this->em->flush();
	}
}

