<?php

namespace App\Command;

use App\Entity\ErrorType;
use App\Entity\EventType;
use App\Entity\ResourceType;
use App\Entity\User;
use App\Service\SIEMLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:siem:log-test',
	description: 'Injecte un log SIEM test pour vérifier le fonctionnement',
)]
class SiemLogTestCommand extends Command {
	public function __construct(
		private readonly EntityManagerInterface $em,
		private readonly SIEMLogger $siemLogger
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->addOption( 'user-id', null, InputOption::VALUE_REQUIRED, 'ID de l\'utilisateur' )
			->addOption( 'event-type', null, InputOption::VALUE_REQUIRED, 'Code de l\'événement' )
			->addOption( 'error-type', null, InputOption::VALUE_REQUIRED, 'Code de l\'erreur' )
			->addOption( 'resource-type', null, InputOption::VALUE_OPTIONAL, 'Code de la ressource' )
			->addOption( 'ip', null, InputOption::VALUE_OPTIONAL, 'Adresse IP', '127.0.0.1' )
			->addOption( 'info', null, InputOption::VALUE_OPTIONAL, 'Infos additionnelles', 'Test via CLI' )
			->addOption( 'result', null, InputOption::VALUE_OPTIONAL, 'Résultat de l\'événement (1 = succès, 0 = échec)', 1 )
			->addOption( 'target-id', null, InputOption::VALUE_OPTIONAL, 'ID cible', null );;
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$userId = $input->getOption('user-id');
		$eventTypeCode = $input->getOption('event-type');
		$errorTypeCode = $input->getOption('error-type');
		$resourceTypeCode = $input->getOption('resource-type');

		if (!$userId || !$eventTypeCode || !$errorTypeCode) {
			$output->writeln('<error>Les options --user-id, --event-type, et --error-type sont requises.</error>');
			return Command::FAILURE;
		}

		$user = $this->em->getRepository(User::class)->find($userId);
		if (!$user) {
			$output->writeln('<error>Utilisateur non trouvé (ID: ' . $userId . ')</error>');
			return Command::FAILURE;
		}

		$eventType = $this->em->getRepository(EventType::class)->findOneBy(['code' => $eventTypeCode]);
		$errorType = $this->em->getRepository(ErrorType::class)->findOneBy(['code' => $errorTypeCode]);
		$resourceType = $resourceTypeCode
			? $this->em->getRepository(ResourceType::class)->findOneBy(['code' => $resourceTypeCode])
			: null;

		if (!$eventType || !$errorType) {
			$output->writeln('<error>EventType ou ErrorType invalide.</error>');
			return Command::FAILURE;
		}

		$this->siemLogger->logEvent(
			$user,
			$errorType,
			$eventType,
			$resourceType,
			(int) $input->getOption('result'),
			$input->getOption('ip'),
			$input->getOption('info'),
			$input->getOption('target-id') ? (int) $input->getOption('target-id') : null
		);

		$output->writeln('<info>Événement SIEM enregistré avec succès 🎉</info>');
		return Command::SUCCESS;
	}
}
