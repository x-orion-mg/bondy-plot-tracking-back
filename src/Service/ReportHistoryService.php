<?php

namespace App\Service;

use App\Entity\Parcel;
use App\Entity\ProjectChild;
use App\Entity\SubProject;
use App\Entity\User;
use App\Exception\CustomException;
use App\Service\image\ImageCropper;
use Doctrine\ORM\EntityManagerInterface;
use Imagick;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ReportHistoryService
{
	private Environment $twig;
	private PdfGeneratorService $pdfGeneratorService;
	private ParameterBagInterface $parameterBag;
	private EntityManagerInterface $entityManager;
	private FileService $fileService;
	private TreeSpeciesService $treeSpeciesService;
	private ImageCropper $cropper;

	public function __construct(Environment $twig, PdfGeneratorService $pdfGeneratorService, ParameterBagInterface $parameterBag, EntityManagerInterface $entityManager, FileService $fileService, TreeSpeciesService $treeSpeciesService, ImageCropper $cropper)
	{
		$this->twig                = $twig;
		$this->pdfGeneratorService = $pdfGeneratorService;
		$this->parameterBag        = $parameterBag;
		$this->entityManager       = $entityManager;
		$this->fileService         = $fileService;
		$this->treeSpeciesService  = $treeSpeciesService;
		$this->cropper             = $cropper;
	}

	/**
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @throws LoaderError|CustomException
	 * @throws \ImagickException
	 */
	public function handleReportHistory(User $user, bool $generatePdf = false): Response
	{
		$imagesDir       = $this->parameterBag->get('kernel.project_dir') . '/public/admin/images/pdf/';
		$imagesRegionDir = $this->parameterBag->get('kernel.project_dir') . '/public/admin/images/regions/';

		$imagesBase64    = $imagesRegionBase64 = [];

		$projectChildList = $this->entityManager->getRepository(ProjectChild::class)->findBy(['user' => $user], ['createdAt' => 'DESC'],);

		$subProjects                = [];
		$totalRestoredHectares      = 0;
		$totalIndirectBeneficiaries = 0;
		$totalSeasonalJobs          = 0;
		$totalSustainableEmployment = 0;
		$totalTCO2Sequestered       = 0;
		$totalPartnerFarmers       = 0;

		if (! empty($projectChildList)) {
			foreach ($projectChildList as $projectChild) {
				$totalRestoredHectares      += (float) $projectChild->getEconomicImpactRestoredHectares();
				$totalIndirectBeneficiaries += (int) $projectChild->getSocialImpactIndirectBeneficiaries();
				$totalSeasonalJobs          += (int) $projectChild->getSocialImpactSeasonalJobs();
				$totalSustainableEmployment += (int) $projectChild->getEconomicImpactSustainableEmployment();
				$totalTCO2Sequestered       += (float) $projectChild->getEnvironmentalImpactCO2();
				$totalPartnerFarmers	   += (int) $projectChild->getSocialImpactPartnerFarmer();
				$subProjectsForChild        = $this->entityManager->getRepository(SubProject::class)->findBy([
					'entityType' => ProjectChild::class,
					'entityId'   => $projectChild->getId()
				]);

				$subProjects = array_merge($subProjects, $subProjectsForChild);
			}
		}
		$projectMetrics = [
			'totalRestoredHectares'      => $totalRestoredHectares,
			'totalIndirectBeneficiaries' => $totalIndirectBeneficiaries,
			'totalSeasonalJobs'          => $totalSeasonalJobs,
			'totalSustainableEmployment' => $totalSustainableEmployment,
			'TCO2Sequestered'            => $totalTCO2Sequestered,
			'totalPartnerFarmers'        => $totalPartnerFarmers,
		];
		$files          = glob($imagesDir . '*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);

		foreach ($files as $filePath) {
			$filename                  = pathinfo($filePath, PATHINFO_FILENAME);
			$imagesBase64[$filename] = $this->fileService->convertFileToBase64($filePath);
		}

		$filesRegions = glob($imagesRegionDir . '*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
		foreach ($filesRegions as $filePath) {
			$filename                        = pathinfo($filePath, PATHINFO_FILENAME);
			$imagesRegionBase64[$filename] = $this->fileService->convertFileToBase64($filePath);
		}
		$projectChildren = $user->getProjectChildren();
		$parcels         = $this->entityManager->getRepository(Parcel::class)->findBy([
			'projectChild' => $projectChildren->toArray()
		]);

		if (!empty($parcels)) {
			$treeSpecies     = $this->treeSpeciesService->aggregateTreeSpecies($parcels);
			$treeSpeciesData = $this->treeSpeciesService->getTreeSpeciesDashboardData($treeSpecies);
			$totalTreeCount  = array_sum(array_column($treeSpeciesData, 'treeCount'));

			// 2. Ajouter les pourcentages et offsets
			$currentOffset = 0;
			foreach ($treeSpeciesData as $i => &$species) {
				$percentage = round(($species['treeCount'] / $totalTreeCount) * 100, 2);
				$species['percentage'] = $percentage;
				$species['dashOffset'] = round(-$currentOffset, 2);
				$currentOffset += $percentage;
				if (!$species['color']) {
					$species['color'] = '#cccccc'; // couleur par défaut
				}
			}
			$treeSpeciesChunks = array_chunk($treeSpeciesData, 8);
		} else {
			$totalTreeCount  = 0;
			$treeSpeciesData = [];
			$treeSpeciesChunks = [];
		}

		$htmlContent = $this->twig->render('admin/pdf/report-template.html.twig', [
			'totalTreeCount'   => $totalTreeCount,
			'user'             => $user,
			'projectChildList' => $projectChildList,
			'subProjects'      => $subProjects,
			'images'           => $imagesBase64,
			'imageRegions'     => $imagesRegionBase64,
			'projectMetrics'   => $projectMetrics,
			'treeSpecies'      => $treeSpeciesChunks,
			'chart'            => $this->generateChartBase64($treeSpeciesData),
		]);

		if ($generatePdf) {
			return $this->pdfGeneratorService->generatePdf($htmlContent);
		}

		return new Response($htmlContent);
	}

	private function generateChartBase64($treeSpeciesData): ?string {
		if (empty($treeSpeciesData)) {
			return null;
		}
		$html = $this->twig->render('admin/pdf/page/chart.html.twig', [
			'treeSpecies' => $treeSpeciesData,
		]);

		$tmpHtml = tempnam(sys_get_temp_dir(), 'html_') . '.html';
		file_put_contents($tmpHtml, $html);

		$tmpImage = tempnam(sys_get_temp_dir(), 'img_') . '.png';

		exec("rsvg-convert -w 230 -h 230 -b none $tmpHtml -o $tmpImage");

		$imageChart = new \Imagick($tmpImage);
		$imageChart->setImageFormat('png');

		$imageChartBase64 = 'data:image/png;base64,' . base64_encode($imageChart->getImageBlob());

		$imageChart->clear();
		$imageChart->destroy();

		// Nettoyage des fichiers temporaires
		unlink($tmpHtml);
		unlink($tmpImage);

		return $imageChartBase64;
	}
}
