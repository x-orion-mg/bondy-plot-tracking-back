<?php

namespace App\Controller\Public;

use App\Entity\ReportHistory;
use App\Service\FileDownloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ReportDownloadController extends AbstractController {

	private EntityManagerInterface $entityManager;
	private FileDownloadService $fileDownloadService;

	public function __construct(  EntityManagerInterface $entityManager, FileDownloadService $fileDownloadService ) {
		$this->entityManager       = $entityManager;
		$this->fileDownloadService = $fileDownloadService;
	}
	#[Route('/report-history/{filename}', name: 'download_report_history')]
	public function downloadReportHistory(Request $request, string $filename): Response {
		$reportHistory = $this->entityManager->getRepository(ReportHistory::class)->findOneBy(['fileName' => $filename]);
		$filePath = $reportHistory ? $reportHistory->getFilePath() : null;

		if ($filePath && file_exists($filePath)) {
			return $this->fileDownloadService->handleFileDownload($request, $filename, $filePath);
		}

		return new Response("File not found", 404);
	}

}
