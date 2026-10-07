<?php

namespace App\Controller\Admin;

use App\Service\CsvProcessorInterface;
use App\Service\CustomerImportService;
use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route( '/admin/users' )]
class CustomerImportController extends AbstractController {
	private CustomerImportService $customerImportService;
	private FileService $fileService;

	public function __construct( CustomerImportService $customerImportService, FileService $fileService ) {
		$this->customerImportService = $customerImportService;
		$this->fileService           = $fileService;
	}

	#[Route( '/{userId}/import', name: 'user_import', methods: [ 'POST' ] )]
	public function import(Request $request, int $userId, CsvProcessorInterface $csvProcessor): Response {
		/** @var UploadedFile $file */
		$file = $request->files->get('csvFile');
		$response = [];

		if ($this->fileService->isInvalidFile($file)) {
			$response = $this->customerImportService->generateModalResponse(
				'Erreur de téléchargement',
				'Veuillez télécharger un fichier CSV valide.',
				'bg-danger-gradient'
			);
		} elseif ($this->customerImportService->isFileAlreadyProcessed($file->getClientOriginalName())) {
			$response = $this->customerImportService->generateModalResponse(
				'Fichier déjà importé',
				'Ce fichier a déjà été importé. Veuillez vérifier que vous ne l\'importez pas en double.',
				'bg-warning-gradient'
			);
		} else {
			$response = $this->customerImportService->processFile($file, $userId, $csvProcessor, $request->getSchemeAndHttpHost());
		}

		return $this->json($response);
	}


}
