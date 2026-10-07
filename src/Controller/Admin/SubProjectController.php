<?php

namespace App\Controller\Admin;

use App\Entity\ProjectParent;
use App\Entity\SubProject;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\SubProjectRepository;
use App\Service\CityService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route( '/admin/{entityType}/{entityId}/subprojects' )]
class SubProjectController extends AbstractController {
	private EntityManagerInterface $entityManager;
	private Packages $packages;
	private RequestStack $requestStack;
	private ProjectChildRepository $projectChildRepository;
	private GalleryRepository $galleryRepository;
	private CityService $cityService;


	public function __construct(
        EntityManagerInterface $entityManager,
        Packages $packages,
        RequestStack $requestStack,
        ProjectChildRepository $projectChildRepository,
        GalleryRepository $galleryRepository,
        CityService $cityService )
    {
		$this->entityManager          = $entityManager;
		$this->requestStack           = $requestStack;
		$this->packages               = $packages;
		$this->projectChildRepository = $projectChildRepository;
		$this->galleryRepository      = $galleryRepository;
		$this->cityService = $cityService;
	}

	/**
	 * Returns the fully qualified class name for a given entity type.
	 *
	 * @param string $entityType The type of the entity (e.g., 'projectParent', 'projectChild').
	 *
	 * @return string The fully qualified class name of the entity, or an empty string if the type is not recognized.
	 */
	private function getEntityClass( string $entityType ): string {
		$entityClasses = [
			'project-parent' => \App\Entity\ProjectParent::class,
			'project-child'  => \App\Entity\ProjectChild::class,
		];

		return $entityClasses[ $entityType ] ?? '';
	}


	#[Route( '/', name: 'subproject_index', methods: [ 'GET' ] )]
	public function index( SubProjectRepository $subProjectRepository, string $entityType, int $parentId ): Response {
		$subProjects = $subProjectRepository->findBy( [ 'entityType' => $entityType, 'entityId' => $parentId ] );

		return $this->render( 'admin/sub-projects/index.html.twig', [
			'subProjects' => $subProjects,
		] );
	}

	private function createSubProject(SubProject $subProject, array $data, array $files, FileUploader $fileUploader, string $baseUrl, string $entityType, int $entityId): void {
		 // Tronquer historyExcerpt à 255 caractères maximum.
		 $historyExcerpt = $data['historyExcerpt'] ?? '';
		 if (strlen($historyExcerpt) > 255) {
			 $historyExcerpt = substr($historyExcerpt, 0, 255);
		 }
		
		// Assigner les valeurs à l'objet SubProject.
		$subProject->setEntityType($this->getEntityClass($entityType));
		$subProject->setEntityId($entityId);
		$subProject->setName($data['name'] ?? '');
		$subProject->setCity($data['city'] ?? '');
		$subProject->setHistoryExcerpt($historyExcerpt); // Utiliser la valeur tronquée de historyExcerpt.
		$subProject->setHistoryContent($data['historyContent'] ?? '');
		$subProject->setLongitude($data['longitude'] ?? 0);
		$subProject->setLatitude($data['latitude'] ?? 0);
		$subProject->setStartDate($this->handleDate($data['startDate'] ?? ''));

		$cityLabel = $data['city'] ?? '';

		if ($entityType === 'project-child') {
			$projectChild = $this->projectChildRepository->find($entityId);
			if ($projectChild) {
				$region = $projectChild->getProjectParent()->getRegion();
				$city = $this->cityService->findOrCreateCity($cityLabel, $region);
				$subProject->setTown($city);
			}
		} elseif ($entityType === 'project-parent') {
			$projectParent = $this->entityManager->getRepository(ProjectParent::class)->find($entityId);
			if ($projectParent) {
				$region = $projectParent->getRegion();
				$city = $this->cityService->findOrCreateCity($cityLabel, $region);
				$subProject->setTown($city);
			}
		}

		if (!empty($files['uploadFile']) && $files['uploadFile']->isValid()) {
			$subProject->setBanner($this->handleFileUpload($files['uploadFile'], $fileUploader, 'uploads/banners', $baseUrl));
		} else {
			$subProject->setBanner($baseUrl . $this->packages->getUrl('uploads/banners/default-banner.jpg'));
		}

		if (!empty($files['iconFile']) && $files['iconFile']->isValid()) {
			$subProject->setIcon($this->handleFileUpload($files['iconFile'], $fileUploader, 'uploads/icons', $baseUrl));
		}
	}

	#[Route('/new', name: 'subproject_new', methods: ['GET', 'POST'])]
	public function new(Request $request, string $entityType, int $entityId, FileUploader $fileUploader): Response {
		$subProject = new SubProject();
		if ($request->isMethod('POST')) {
			$data = $request->request->all();
			$files = $request->files->all();
			$baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

			$this->createSubProject($subProject, $data, $files, $fileUploader, $baseUrl, $entityType, $entityId);

			$this->entityManager->persist($subProject);
			$this->entityManager->flush();

			$editUrl = $this->generateUrl('subproject_edit', [
				'entityType' => $entityType,
				'entityId' => $entityId,
				'id' => $subProject->getId()
			]);
			return $this->json(['success' => true, 'editUrl' => $editUrl]);
		}

		if ($request->isMethod('GET') && 'project-child' === $entityType) {
			$projectChild = $this->projectChildRepository->find($entityId);
			$localisation = $projectChild ? $projectChild->getProjectParent()->getLocalisation() : null;

			return $this->render('admin/sub-projects/new.html.twig', [
				'subProject'   => $subProject,
				'entityId'     => $entityId,
                'entityType'   => $entityType,
				'localisation' => $localisation,
			]);
		}

		return $this->json(['success' => false, 'message' => 'Méthode de requête non valide.']);
	}

	#[Route( '/{id}/show', name: 'subproject_show', methods: [ 'GET' ] )]
	public function show( SubProject $subProject ): Response {
		return $this->render( 'admin/sub-projects/show.html.twig', [
			'subProject' => $subProject,
		] );
	}
	private function handleFileUpload($file, FileUploader $fileUploader, string $directory, string $baseUrl): string {
		$fileUploader->setTargetDirectory($directory);
		$fileName = $fileUploader->upload($file);
		return $baseUrl . $this->packages->getUrl($directory . '/' . $fileName['fileName']);
	}

	private function handleDate($dateString): \DateTime {
		$date = \DateTime::createFromFormat('d/m/Y', $dateString);
		return $date ?: new \DateTime('now');
	}

	private function updateSubProject(SubProject $subProject, array $data, array $files, FileUploader $fileUploader, string $baseUrl): void {
		$subProject->setName($data['name'] ?? $subProject->getName());
		$subProject->setCity($data['city'] ?? $subProject->getCity());
		$subProject->setHistoryExcerpt($data['historyExcerpt'] ?? $subProject->getHistoryExcerpt());
		$subProject->setHistoryContent($data['historyContent'] ?? $subProject->getHistoryContent());
		$subProject->setLongitude($data['longitude'] ?? $subProject->getLongitude());
		$subProject->setLatitude($data['latitude'] ?? $subProject->getLatitude());
		$subProject->setStartDate($this->handleDate($data['startDate'] ?? ''));

		if (isset($files['uploadFile']) && $files['uploadFile']->isValid()) {
			$subProject->setBanner($this->handleFileUpload($files['uploadFile'], $fileUploader, 'uploads/banners', $baseUrl));
		}

		if (isset($files['iconFile']) && $files['iconFile']->isValid()) {
			$subProject->setIcon($this->handleFileUpload($files['iconFile'], $fileUploader, 'uploads/icons', $baseUrl));
		}
	}
	#[Route( '/{id}/edit', name: 'subproject_edit', methods: [ 'GET', 'POST' ] )]
	public function edit(Request $request, SubProject $subProject, string $entityType, int $entityId, FileUploader $fileUploader): Response {
		if ($request->isMethod('POST')) {
			$data = $request->request->all();
			$files = $request->files->all();
			$baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

			$subProject->setEntityType($this->getEntityClass($entityType));
			$subProject->setEntityId($entityId);
			$this->updateSubProject($subProject, $data, $files, $fileUploader, $baseUrl);

			$this->entityManager->persist($subProject);
			$this->entityManager->flush();

			return $this->json(['success' => true]);
		}

		if ($request->isMethod('GET') && 'project-child' === $entityType) {
			$projectChild = $this->projectChildRepository->find($entityId);
			$localisation = $projectChild ? $projectChild->getProjectParent()->getLocalisation() : null;
			$galleries = $this->galleryRepository->findBy(['entityType' => SubProject::class, 'entityId' => $subProject->getId()]);

			return $this->render('admin/sub-projects/edit.html.twig', [
				'subProject' => $subProject,
				'entityId' => $entityId,
				'entityType' => $entityType,
				'localisation' => $localisation,
				'galleries' => $galleries,
			]);
		}

		return $this->json(['success' => false, 'message' => 'Méthode de requête non valide.'], Response::HTTP_BAD_REQUEST);
	}

	private function findSubProject(int $id): ?SubProject {
		return $this->entityManager->getRepository(SubProject::class)->find($id);
	}

	#[Route('/{id}/delete', name: 'subproject_delete', methods: ['DELETE'])]
	public function delete(int $id): Response {
		$subProject = $this->findSubProject($id);

		if (!$subProject) {
			return $this->json(['success' => false, 'message' => 'Le sous-projet n\'a pas été trouvé'], Response::HTTP_NOT_FOUND);
		}
		$this->entityManager->remove($subProject);
		$this->entityManager->flush();

		return $this->json(['success' => true], Response::HTTP_OK);
	}
}
