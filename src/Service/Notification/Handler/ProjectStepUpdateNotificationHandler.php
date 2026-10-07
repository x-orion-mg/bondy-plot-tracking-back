<?php

namespace App\Service\Notification\Handler;

use App\Entity\ProjectChild;
use App\Service\MailService;
use App\Service\Notification\EntityNotificationHandlerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class ProjectStepUpdateNotificationHandler implements EntityNotificationHandlerInterface {
	private string $frontendBaseUrl;

	public function __construct(
		private readonly MailService $mailService,
		ParameterBagInterface $params
	) {
		$this->frontendBaseUrl = $params->get( 'frontend_base_url' );
	}

	public function supports( object $entity, ?object $context = null ): bool {
		$isValidEntity   = $entity instanceof ProjectChild;
		$isValidContext  = $context instanceof PreUpdateEventArgs;
		$hasChangedField = $isValidContext && $context->hasChangedField( 'projectStep' );

		if ( ! $isValidEntity || ! $isValidContext || ! $hasChangedField ) {
			return false;
		}

		$old = $context->getOldValue( 'projectStep' );
		$new = $context->getNewValue( 'projectStep' );

		return ( $old?->getId() ?? null ) !== ( $new?->getId() ?? null );
	}


	/**
	 * @throws SyntaxError
	 * @throws TransportExceptionInterface
	 * @throws RuntimeError
	 * @throws LoaderError
	 */
	public function handle( object $entity, ?object $context = null ): void {

		if ( ! ( $entity instanceof ProjectChild ) || ! ( $context instanceof PreUpdateEventArgs ) ) {
			return;
		}

		$user = $entity->getUser();
		if ( ! $user && $user->getEmail() === null) {
			return;
		}

		$link = rtrim( $this->frontendBaseUrl, '/' ) . "/dashboard/?token=" . $user->getToken();

		$this->mailService->sendEmail(
			$user->getEmail(),
			"[BONDY] - Latest Dashboard Insights",
			'emails/entity_notification.html.twig',
			[
				'contentTemplate' => 'project_step_update.html.twig',
				'title'           => 'Check out the latest updates on your dashboard ✨',
				'subtitle'        => 'We’ve got some news for you!',
				'message'         => 'Your project status has been updated reflecting the progress made so far (with more to come!).',
				'button_text'     => 'News',
				'button_link'     => $link,

			],
			$entity
		);
	}
}
