<?php

namespace App\EventListener;


use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Service\Notification\EntityNotificationManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityNotificationListener
{
	public function __construct(
		private readonly EntityNotificationManager $notificationManager
	) {}
	#[AsEventListener( event: 'postPersist', priority: 0 )]
	public function postPersist(LifecycleEventArgs $args): void
	{
		$this->handle($args->getObject(), $args);
	}
	#[AsEventListener(event: 'preUpdate', priority: 0)]
	public function preUpdate(PreUpdateEventArgs $args): void
	{
		$this->handle($args->getObject(), $args);
	}

	private function handle(object $entity, $args): void
	{
		$this->notificationManager->notify($entity, $args);
	}
}

