<?php

namespace App\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'app:clear-tables',
	description: 'Vide certaines tables de la base de données (avec option --dry-run)'
)]
class ClearTablesCommand extends Command {
	public function __construct( private EntityManagerInterface $em ) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption(
				'dry-run',
				null,
				InputOption::VALUE_NONE,
				'Affiche les tables qui seraient vidées, sans exécuter les suppressions.'
			);
	}

	/**
	 * @throws Exception
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$connection = $this->em->getConnection();
		$platform   = $connection->getDatabasePlatform();

		// Liste des tables à vider
		$tablesToTruncate = [
			'sub_project',
			'story_telling',
			'gallery',
			'allocated_tree',
			'certificate',
			'customer_import',
			'customer_import_user',
			'impact_project',
			'parcel_tree_species',
			'parcel',
			'partner',
			'partner_customer',
			'project_child',
			'report_history',
			'tree_species'
		];

		$dryRun = $input->getOption('dry-run');

		$output->writeln($dryRun
			? '<info>[Dry-run] Tables qui seraient vidées :</info>'
			: '<info>--- Vidage des tables ---</info>'
		);

		if (!$dryRun) {
			$connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
		}

		foreach ($tablesToTruncate as $table) {
			if ($dryRun) {
				$output->writeln("🧪 [Dry-run] $table");
			} else {
				$connection->executeStatement($platform->getTruncateTableSQL($table, true));
				$output->writeln("✅ Table <comment>$table</comment> vidée.");
			}
		}

		if (!$dryRun) {
			$connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
			$output->writeln('<info>Toutes les tables ont été vidées.</info>');
		}

		return Command::SUCCESS;
	}
}
