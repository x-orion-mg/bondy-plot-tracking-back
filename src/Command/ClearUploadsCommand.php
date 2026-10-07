<?php

namespace App\Command;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
	name: 'app:clear-uploads',
	description: 'Supprime les fichiers dans /public/uploads (avec option --dry-run)'
)]
class ClearUploadsCommand extends Command
{
	public function __construct(private readonly ParameterBagInterface $params)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption(
				'dry-run',
				null,
				InputOption::VALUE_NONE,
				'Affiche les fichiers qui seraient supprimés sans les supprimer.'
			);
	}

	#[NoReturn] protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$filesystem = new Filesystem();
		$uploadDir = $this->params->get('kernel.project_dir') . '/public/uploads';
		$dryRun = $input->getOption('dry-run');

		$output->writeln($dryRun
			? '<info>[Dry-run] Contenu à supprimer dans /public/uploads :</info>'
			: '<info>--- Suppression des fichiers dans /public/uploads ---</info>'
		);

		if (!$filesystem->exists($uploadDir)) {
			$output->writeln('<comment>Le dossier /uploads n’existe pas.</comment>');
			return Command::SUCCESS;
		}

		$finder = new Finder();
		$finder->in($uploadDir)->depth(0); // tu peux mettre '>= 0' pour récursif

		if ($finder->hasResults()) {
			foreach ($finder as $file) {
				$path = $file->getRealPath();

				if ($dryRun) {
					$output->writeln("🧪 [Dry-run] $path");
				} else {
					$filesystem->remove($path);
					$output->writeln("🗑️ Supprimé : $path");
				}
			}
		} else {
			$output->writeln('<comment>Aucun fichier trouvé dans /uploads.</comment>');
		}

		if ($dryRun) {
			$output->writeln('<info>[Dry-run] Aucune suppression réelle effectuée.</info>');
		} else {
			$output->writeln('<info>✅ Tous les fichiers ont été supprimés.</info>');
		}

		return Command::SUCCESS;
	}
}
