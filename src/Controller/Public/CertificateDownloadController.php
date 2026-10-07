<?php

namespace App\Controller\Public;

use App\Entity\Certificate;
use App\Service\FileDownloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CertificateDownloadController extends AbstractController {

	private EntityManagerInterface $entityManager;
	private FileDownloadService $fileDownloadService;

	public function __construct(  EntityManagerInterface $entityManager, FileDownloadService $fileDownloadService ) {
		$this->entityManager       = $entityManager;
		$this->fileDownloadService = $fileDownloadService;
	}
	#[Route('/certificate/{filename}', name: 'download_certificate')]
	public function downloadCertificate(Request $request, string $filename): Response {
		$certificate = $this->entityManager->getRepository(Certificate::class)->findOneBy(['fileName' => $filename]);
		$filePath = $certificate ? $certificate->getFilePath() : null;

		if ($filePath && file_exists($filePath)) {
			return $this->fileDownloadService->handleFileDownload($request, $filename, $filePath);
		}

		return new Response("File not found", 404);
	}

}
