<?php

namespace App\Service\Notification\Handler;

use App\Entity\ProjectChild;
use App\Entity\StoryTelling;
use App\Entity\SubProject;
use App\Service\MailService;
use App\Service\Notification\EntityNotificationHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsTaggedItem( index: 'entity_notification_handler', priority: 10 )]
class NewSubProjectNotificationHandler implements EntityNotificationHandlerInterface {
	private string $frontendBaseUrl;

	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly MailService $mailService,
		ParameterBagInterface $params
	) {
		$this->frontendBaseUrl = $params->get( 'frontend_base_url' );
	}

	public function supports( object $entity, ?object $context = null ): bool {
		return $entity instanceof SubProject &&
		       $entity->getEntityType() === ProjectChild::class;
	}

	/**
	 * @throws SyntaxError
	 * @throws TransportExceptionInterface
	 * @throws RuntimeError
	 * @throws LoaderError
	 */
	public function handle( object $entity, ?object $context = null ): void {
		$projectChildId = $entity->getEntityId();
		$projectChild   = $this->entityManager->getRepository( ProjectChild::class )->find( $projectChildId );
		if ( ! $projectChild ) {
			return;
		}

		$user = $projectChild->getUser();
		if ( ! $user && $user->getEmail() === null) {
			return;
		}
		$parentId     = $projectChild->getProjectParent()->getId();
		$userId       = $user->getId();
		$subProjectId = $entity->getId();
		$link         = rtrim( $this->frontendBaseUrl, '/' ) . "/parents/$parentId/partners/$userId/sub-project/$subProjectId/?token=" . $user->getToken();

		$this->mailService->sendEmail(
			$user->getEmail(),
			"[BONDY] - Latest Dashboard Insights",
			'emails/entity_notification.html.twig',
			[
				'contentTemplate' => 'new_sub_project.html.twig',
				'title'           => 'Check out the latest updates on your dashboard ✨',
				'subtitle'        => 'We’ve got some news for you!',
				'message'         => 'A new subproject has been added to your main project — giving you even more insight into what’s happening on the ground.',
				'button_text'     => 'News',
				'button_link'     => $link,
			],
			$entity
		);
	}
}
