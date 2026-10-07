<?php

namespace App\Controller\Admin;

use App\Entity\ProjectParent;
use App\Entity\SubProject;
use App\Form\SubProjectType;
use App\Repository\ProjectParentRepository;
use App\Repository\ProjectTypeRepository;
use App\Repository\ProjectStatusRepository;
use App\Service\FileUploader;
use App\Service\ServiceUploadFiles;
use App\Service\TreeService;
use App\Utils\PathName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route( '/admin/projects/parents' )]
class ProjectParentController extends AbstractController {
	public function __construct(
		private ProjectParentRepository $projectParentRepository,
		private ProjectTypeRepository $projectTypeRepository,
		private ProjectStatusRepository $projectStatusRepository,
		private EntityManagerInterface $entityManager,
	) {
		$this->projectParentRepository = $projectParentRepository;
		$this->projectTypeRepository   = $projectTypeRepository;
		$this->projectStatusRepository = $projectStatusRepository;
		$this->entityManager           = $entityManager;
	}

	#[Route( '/', name: 'project_parent_list', methods: [ 'GET', 'POST' ] )]
	public function index(): Response {
		return $this->render( 'admin/project-parents/index.html.twig', [
			'types'  => $this->projectTypeRepository->findAll(),
			'status' => $this->projectStatusRepository->findAll(),
		] );
	}

    #[Route('/list-ajax', name: 'project_parent_list_ajax', methods: ['GET'])]
    public function listAjax(Request $request): JsonResponse
    {
        $params = $request->query->all();
        $draw = (int)$request->query->get('draw', 1);
        $statusId = array_key_exists('status_id', $params) && !empty($params['status_id']) ? $params['status_id'] : 0;
        $typeId = array_key_exists('type_id', $params) && !empty($params['type_id']) ? $params['type_id'] : 0;

        // Pagination parameters
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = isset($params['search']) && isset($params['search']['value']) ? $params['search']['value'] : '';

        $projects = $this->projectParentRepository->getAllProjectParent($search, $start, $length, $statusId, $typeId, false);
        $filteredCount = $this->projectParentRepository->countAllFilteredProjectParent($search, $statusId, $typeId);
        $totalCount = $this->projectParentRepository->countAllProjectParent($statusId, $typeId);

        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => array_map(function ($project) {
                return $project;
            }, $projects),
        ];

        return new JsonResponse($data);
    }

	#[Route( '/{id}/show', name: 'project_parent_show', methods: [ 'GET' ] )]
	public function show( ?int $id ): Response {
		$projectParentRepository = $this->entityManager->getRepository( ProjectParent::class );
		$projectParent           = $projectParentRepository->fetchProjectParentById( $id );

		return $this->render( 'admin/project-parents/show.html.twig', [
			'projectParent' => $projectParent,
		] );
	}


	#[Route( '/{id}/edit', name: 'project_parent_edit', methods: [ 'GET', 'POST' ] )]
	public function edit( Request $request, int $id, FileUploader $fileUploader, ServiceUploadFiles $serviceImage, TreeService $treeService ): Response {
		$projectParentRepository = $this->entityManager->getRepository( ProjectParent::class );
		$projectParent           = $projectParentRepository->fetchProjectParentById( $id );
		$subProject              = new SubProject();
		$formSubProject          = $this->createForm( SubProjectType::class, $subProject );

		if ( $request->isMethod( 'POST' ) ) {
			$data  = $request->request->all();
			$files = $request->files->all();

			// Update the projectParent entity with the request data
			$projectParent->setTitle( $data['title'] ?? $projectParent->getTitle() );
			$projectParent->setCity( $data['city'] ?? $projectParent->getCity() );

			$projectParent->setHistory( $data['history'] ?? $projectParent->getHistory() );

			if ( isset( $data['projectType'] ) ) {
				$projectParent->setProjectType( $this->projectTypeRepository->find( $data['projectType'] ) );
			}

			if ( isset( $data['startDate'] ) ) {
				$startDate = \DateTime::createFromFormat( 'd/m/Y', $data['startDate'] );
				if ( $startDate ) {
					$projectParent->setStartDate( $startDate );
				} else {
					// Handle invalid date format
					$projectParent->setStartDate( new \DateTime( 'now' ) );
				}
			}

			if ( isset( $data['projectStatus'] ) ) {
				$projectParent->setProjectStatus( $this->projectStatusRepository->find( $data['projectStatus'] ) );
			}

			if ( isset( $files['uploadFile'] ) && $files['uploadFile']->isValid() ) {
				$fileUrl = $serviceImage->uploadBannerImage( PathName::UPLOAD_BANNER_PROJECT_PARENT, $files );
				$projectParent->setBannerImage( $fileUrl );
			}

			$this->entityManager->flush();
			$this->addFlash( 'success', 'Modification effectuée avec succès' );

			return $this->redirectToRoute( 'project_parent_edit', [ 'id' => $id ] );
		}

		return $this->render( 'admin/project-parents/edit.html.twig', [
			'projectTypes'   => $this->projectTypeRepository->findAll(),
			'projectStatus'  => $this->projectStatusRepository->findAll(),
			'projectParent'  => $projectParent,
			'treeCount'      => $treeService->getTreeCountForProjectParent( $projectParent ),
			'formSubProject' => $formSubProject->createView(),
		] );
	}


	#[Route( '/{id}/delete', name: 'project_parent_delete', methods: [ 'DELETE' ] )]
	public function delete( Request $request, ProjectParent $projectParent ): Response {
		if ( $this->isCsrfTokenValid( 'delete' . $projectParent->getId(), $request->request->get( '_token' ) ) ) {
			$this->entityManager->remove( $projectParent );
			$this->entityManager->flush();
		}

		return $this->redirectToRoute( 'project_parent_list' );
	}

	#[Route( '/parent-location', name: 'project_parent_location', methods: [ 'GET' ] )]
	public function getParentLocation( Request $request ): JsonResponse {
		if ( ! $this->getUser() ) {
			return $this->json( [
				'success' => false,
				'message' => 'L\'utilisateur n\'est pas authentifié'
			], Response::HTTP_UNAUTHORIZED );
		}
		$parentId = $request->query->getInt( 'parent_id', 0 );
		if ( $parentId <= 0 ) {
			return $this->json( [
				'success' => false,
				'message' => 'Numéro d\'identification du parent invalide'
			], Response::HTTP_BAD_REQUEST );
		}
		$localisation = $this->projectParentRepository->getDataParentLocation( $parentId );

		return $this->json( [
			'success' => true,
			'data'    => $localisation
		], Response::HTTP_OK );
	}
}
