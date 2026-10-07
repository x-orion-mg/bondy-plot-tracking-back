<?php

namespace App\EventSubscriber;

use App\Entity\ResourceType;
use App\Entity\SIEMEntry;
use App\Entity\User;
use App\Entity\EventType;
use App\Entity\ErrorType;
use App\Service\SIEMLogger;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DoctrineEntityLoggerSubscriber implements EventSubscriber
{
	public function __construct(
		private readonly SIEMLogger $siemLogger,
		private readonly Security $security,
		private readonly EntityManagerInterface $entityManager,
	) {}

	public function getSubscribedEvents(): array
	{
		return [
			Events::postPersist,
			Events::postUpdate,
			Events::postRemove,
		];
	}
	#[AsEventListener( event: 'postPersist', priority: 2 )]
	public function postPersist(LifecycleEventArgs $args): void
	{
		$this->logChange($args, 'CREATE');
	}
	#[AsEventListener( event: 'postUpdate', priority: 2 )]
	public function postUpdate(LifecycleEventArgs $args): void
	{
		$this->logChange($args, 'UPDATE');
	}

	#[AsEventListener( event: 'postRemove', priority: 2 )]
	public function postRemove(LifecycleEventArgs $args): void
	{
		$this->logChange($args, 'DELETE');
	}

	private function logChange(LifecycleEventArgs $args, string $action): void
	{
		$entity = $args->getObject();

		// Ne pas logger les logs eux-mêmes
		if ($entity instanceof SIEMEntry) {

			return;
		}


		// Récupérer l'utilisateur connecté
		$user = $this->security->getUser();
		if (!$user instanceof User) {
			return;
		}

		// Générer dynamiquement l'eventType (ex: ENTITY_CREATE, ENTITY_UPDATE)
		$eventTypeCode = 'ENTITY_' . $action;
		$eventType = $this->entityManager->getRepository(EventType::class)->findOneBy(['code' => $eventTypeCode]);

		// Aucun type ? -> on ne logge pas
		if (!$eventType) {
			return;
		}

		$errorType = $this->entityManager->getRepository(ErrorType::class)->findOneBy(['code' => 'NONE']);

		$entityClass = (new \ReflectionClass($entity))->getShortName();
		$resourceTypeCode = strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $entityClass));
		$resourceType = $this->entityManager->getRepository(ResourceType::class)->findOneBy(['code' => $resourceTypeCode]);

		$targetId = method_exists($entity, 'getId') ? $entity->getId() : null;

		$info = sprintf('%s de %s (ID: %s)', $action, $entityClass, $targetId ?? 'n/a');

		$this->siemLogger->logEvent(
			$user,
			$errorType,
			$eventType,
			$resourceType,
			1,
			null,
			$info,
			$targetId
		);
	}
}
