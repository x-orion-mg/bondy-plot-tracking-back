<?php

namespace App\Controller\Admin;

use App\Entity\ReportHistory;
use App\Entity\User;
use App\Exception\CustomException;
use App\Service\DeleteReportHistoryHandler;
use App\Service\FileDownloadService;
use App\Service\FileService;
use App\Service\PdfGeneratorService;
use App\Service\ReportHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ReportHistoryController extends AbstractController {
	private PdfGeneratorService $pdfGeneratorService;
	private DeleteReportHistoryHandler $deleteHandler;
	private EntityManagerInterface $entityManager;
	private FileDownloadService $fileDownloadService;

	public function __construct( PdfGeneratorService $pdfGeneratorService, EntityManagerInterface $entityManager, DeleteReportHistoryHandler $deleteHandler, FileDownloadService $fileDownloadService ) {
		$this->pdfGeneratorService = $pdfGeneratorService;
		$this->entityManager       = $entityManager;
		$this->deleteHandler       = $deleteHandler;
		$this->fileDownloadService = $fileDownloadService;
	}

	/**
	 * @throws SyntaxError
	 * @throws RuntimeError
	 * @throws LoaderError
	 * @throws CustomException
	 * @throws \ImagickException
	 */
	#[Route( '/{user}/users/generate-report-history-pdf', name: 'user_generate_report_pdf', methods: [ 'POST' ] )]
	public function generateReportHistoryPdf( User $user, ReportHistoryService $reportHistoryService, SluggerInterface $slugger, FileService $fileService ): Response {
		$pdfContent =  $reportHistoryService->handleReportHistory( $user, true );
		$slug      = $slugger->slug($user->getLastName());
		$fileName  = sprintf('report_%s_%s.pdf', $slug, (new \DateTime())->format('Ymd-His'));
		$filePath  = sprintf('uploads/report/%s/%s', $slug, $fileName);
		$directory = dirname($filePath);

		// Assurer que le répertoire existe
		$fileService->ensureDirectoryExists($directory);

		file_put_contents($filePath, $pdfContent);

		$report = new ReportHistory();
		$report->setFileName($fileName);
		$report->setUser($user);
		$report->setFilePath($filePath);

		try {
			$this->entityManager->persist($report);
			$this->entityManager->flush();
		} catch (\Exception $e) {
			return $this->json([
				'success' => false,
				'message' => 'Échec de la sauvegarde du rapport.'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->json([
			'success' => true,
			'message' => 'PDF généré et sauvegardé avec succès.'
		], Response::HTTP_OK);
	}

	#[Route( '/{user}/users/{id}/delete-report-history', name: 'delete_report_history', methods: [ 'DELETE' ] )]
	public function deleteReportHistory( User $user, int $id ): Response {
		return $this->deleteHandler->handleDelete( $id, $user->getId() );
	}
}
