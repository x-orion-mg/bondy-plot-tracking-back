<?php
namespace App\EventSubscriber;

use App\Entity\ResourceType;
use App\Service\SIEMLogger;
use App\Entity\EventType;
use App\Entity\ErrorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Bundle\SecurityBundle\Security;

class MailerSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly SIEMLogger $logger,
		private readonly EntityManagerInterface $em,
		private readonly Security $security,
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			MessageEvent::class => 'onEmailSent',
			FailedMessageEvent::class => 'onEmailFailed',
		];
	}

	public function onEmailSent(MessageEvent $event): void
	{
		$user = $this->security->getUser();

		if (!$user instanceof \App\Entity\User) {
			// Pas d'utilisateur (commande CLI, CRON, ou email système)
			return;
		}
		$email = $event->getMessage();
		$triggeringEvent = 'Unknown';
		if (method_exists($email, 'getHeaders')) {
			$headers = $email->getHeaders();
			if ($headers->has('X-Triggering-Entity')) {
				$triggeringEvent = $headers->get('X-Triggering-Entity')->getBody();
			}
		}
		$eventType = $this->em->getRepository(EventType::class)->findOneBy(['code' => 'EMAIL_SENT']);
		$errorType = $this->em->getRepository(ErrorType::class)->findOneBy(['code' => 'NONE']);
		$resourceType = $this->em->getRepository(ResourceType::class)->findOneBy(['code' => 'EMAIL']);

		$this->logger->logEvent(
			$user,
			$errorType,
			$eventType,
			$resourceType,
			1,
			null,
			'Email envoyé à ' . $event->getEnvelope()->getRecipients()[0]->getAddress() .
			' déclenché par l\'événement : '.$triggeringEvent
		);
	}
	public function onEmailFailed(FailedMessageEvent $event): void
	{
		$user = $this->security->getUser();
		if (!$user instanceof \App\Entity\User) {
			return;
		}

		$eventType = $this->em->getRepository(EventType::class)->findOneBy(['code' => 'EMAIL_FAILURE']);
		$errorType = $this->em->getRepository(ErrorType::class)->findOneBy(['code' => 'EMAIL_EXCEPTION']);

		$message = $event->getMessage();
		$triggeringEvent = 'Unknown';
		if (method_exists($message, 'getHeaders')) {
			$headers = $message->getHeaders();
			if ($headers->has('X-Triggering-Entity')) {
				$triggeringEvent = $headers->get('X-Triggering-Entity')->getBody();
			}
		}
		$recipient = 'N/A';
		if (method_exists($message, 'getTo')) {
			$to = $message->getTo();
			if (!empty($to)) {
				$recipient = $to[0]->getAddress();
			}
		}

		$exception = $event->getError();
		$errorMessage = $exception->getMessage() ? $exception->getMessage() : 'Erreur inconnue';

		$this->logger->logEvent(
			$user,
			$errorType,
			$eventType,
			null,
			0,
			null,
			'Échec d’envoi d’email à ' . $recipient . ' : ' . $errorMessage,
			$triggeringEvent
		);
	}
}
