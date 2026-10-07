<?php
namespace App\EventSubscriber;

use App\Entity\ErrorType;
use App\Entity\EventType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Service\SIEMLogger;
use Doctrine\ORM\EntityManagerInterface;

class LoginSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly SIEMLogger $siemLogger,
		private readonly EntityManagerInterface $em
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			InteractiveLoginEvent::class => 'onLoginSuccess',
		];
	}

	public function onLoginSuccess(InteractiveLoginEvent $event): void
	{
		$user = $event->getAuthenticationToken()->getUser();

		$eventType = $this->em->getRepository(EventType::class)->findOneBy(['code' => 'LOGIN_SUCCESS']);
		$errorType = $this->em->getRepository(ErrorType::class)->findOneBy(['code' => 'NONE']);

		$this->siemLogger->logEvent(
			$user,
			$errorType,
			$eventType,
			null,
			1,
			$event->getRequest()->getClientIp()
		);
	}
}
