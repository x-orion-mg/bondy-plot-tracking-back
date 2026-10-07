<?php

namespace App\Service\Notification;

class EntityNotificationManager {
	/**
	 * @param iterable<EntityNotificationHandlerInterface> $handlers
	 */
	public function __construct(private readonly iterable $handlers) {}

	public function notify(object $entity, ?object $context = null): void
	{
		foreach ($this->handlers as $handler) {
			try {
				// Check if the handler supports the entity and context
				if ($handler->supports($entity, $context)) {
					// Handle the notification
					$handler->handle($entity, $context);
					return;
				}
			} catch (\Throwable $e) {
				dd($e);
				continue;
			}
		}
	}
}
