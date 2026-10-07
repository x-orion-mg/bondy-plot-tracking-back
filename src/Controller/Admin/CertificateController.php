<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Entity\User;
use App\Service\DeleteCertificateHandler;
use App\Service\FileDownloadService;
use App\Service\FileService;
use App\Service\PdfGeneratorService;
use App\Service\ReportHistoryService;
use App\Service\TreeSpeciesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Asset\Packages;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route('/admin')]
class CertificateController extends AbstractController
{

	private PdfGeneratorService $pdfGeneratorService;
	private DeleteCertificateHandler $deleteHandler;
	private EntityManagerInterface $entityManager;

	public function __construct(PdfGeneratorService $pdfGeneratorService, EntityManagerInterface $entityManager, DeleteCertificateHandler $deleteHandler)
	{
		$this->pdfGeneratorService = $pdfGeneratorService;
		$this->entityManager       = $entityManager;
		$this->deleteHandler       = $deleteHandler;
	}

	#[Route('/{user}/users/generate-certificate-pdf', name: 'user_generate_certificate_pdf', methods: ['POST'])]
	public function generateCertificatePdf(User $user, TreeSpeciesService $treeSpeciesService, SluggerInterface $slugger, FileService $fileService): Response
	{

		$totalTreeCount = $treeSpeciesService->calculateTotalTreeCountForUser($user);
		$htmlContent    = $this->renderView('admin/pdf/certificate-template.html.twig', [
			'user'           => $user,
			'totalTreeCount' => $totalTreeCount,
		]);

		$pdfContent = $this->pdfGeneratorService->generatePdf($htmlContent);

		$slug      = $slugger->slug($user->getLastName());
		$fileName  = sprintf('certificate_%s_%s.pdf', $slug, (new \DateTime())->format('Ymd-His'));
		$filePath  = sprintf('uploads/certificate/%s/%s', $slug, $fileName);
		$directory = dirname($filePath);

		// Assurer que le répertoire existe
		$fileService->ensureDirectoryExists($directory);

		file_put_contents($filePath, $pdfContent);

		$certificate = new Certificate();
		$certificate->setFileName($fileName);
		$certificate->setUser($user);
		$certificate->setFilePath($filePath);

		try {
			$this->entityManager->persist($certificate);
			$this->entityManager->flush();
		} catch (\Exception $e) {
			return $this->json([
				'success' => false,
				'message' => 'Échec de l\'enregistrement du certificat.'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->json([
			'success' => true,
			'message' => 'PDF généré et sauvegardé avec succès.'
		], Response::HTTP_OK);
	}

	#[Route('/{user}/users/{id}/delete-certificate', name: 'delete_certificate', methods: ['DELETE'])]
	public function deleteCertificate(User $user, int $id): Response
	{
		return $this->deleteHandler->handleDelete($id, $user->getId());
	}

	#[Route('/template-certificate', name: 'template_certificate', methods: ['GET'])]
	public function showCertificate(EntityManagerInterface $entityManager): Response
	{
		$user = $entityManager->getRepository(User::class)->find(1);

		return $this->render(
			'admin/pdf/certificate-template.html.twig',
			[
				'totalTreeCount' => 120000,
				'user'           => $user,
			]
		);
	}

	/**
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @throws LoaderError
	 */
	#[Route('/{userId}/template-report-history', name: 'template_report_history', methods: ['GET'])]
	public function showReport(int $userId, ReportHistoryService $reportHistoryService, EntityManagerInterface $entityManager): Response {
		$user = $entityManager->getRepository(User::class)->find($userId);

		if (!$user) {
			throw $this->createNotFoundException('Utilisateur introuvable.');
		}

		return $reportHistoryService->handleReportHistory($user, false);
	}
}
