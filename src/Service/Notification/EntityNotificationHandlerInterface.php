<?php

namespace App\Service\Notification;

interface EntityNotificationHandlerInterface
{
	public function supports(object $entity, ?object $context = null): bool;

	public function handle(object $entity, ?object $context = null): void;
}
