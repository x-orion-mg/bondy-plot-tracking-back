<?php

namespace App\Controller\Admin;

use App\Entity\AllocatedTree;
use App\Entity\ProjectChild;
use App\Exception\CustomException;
use App\Repository\GalleryRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\ProjectStepRepository;
use App\Repository\UserRepository;
use App\Repository\ParcelRepository;
use App\Repository\StoryTellingRepository;
use App\Repository\SubProjectRepository;
use App\Repository\ProfileRepository;
use App\Utils\PathName;
use App\Form\ProjectChildType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\FileUploader;
use App\Service\ServiceUploadFiles;

#[Route( '/admin/projects/child' )]
class ProjectChildController extends AbstractController
{

	public function __construct(
        private ProjectChildRepository $projectChildRepository,
        private UserRepository      $partnerRepository,
        private ProjectStepRepository  $projectStepRepository,
        private SubProjectRepository   $subProjectRepository,
        private GalleryRepository      $galleryRepository,
        private StoryTellingRepository $storyTellingRepository,
        private ParcelRepository       $parcelRepository,
        private ServiceUploadFiles     $serviceImage,
    ) {
		$this->projectChildRepository  = $projectChildRepository;
		$this->partnerRepository       = $partnerRepository;
		$this->projectStepRepository   = $projectStepRepository;
		$this->subProjectRepository    = $subProjectRepository;
		$this->galleryRepository       = $galleryRepository;
		$this->storyTellingRepository  = $storyTellingRepository;
		$this->parcelRepository        = $parcelRepository;
		$this->serviceImage            = $serviceImage;
	}

	#[Route( '/', name: 'project_child_index', methods: [ 'GET' ] )]
	public function index( ProfileRepository $profileRepository ): Response {
		$profile  = $profileRepository->getProfileByCode('PARTNER');
		return $this->render( 'admin/project-child/index.html.twig', [
			'partners'      => $this->partnerRepository->findAllUserPartner( $profile->getId() ),
			'project_steps' => $this->projectStepRepository->findAll(),
		] );
	}

	#[Route( '/list-ajax', name: 'project_child_list_ajax', methods: [ 'GET' ] )]
	public function listAjax( Request $request ): JsonResponse {
		$params    = $request->query->all();
		$draw      = (int) $request->query->get( 'draw', 1 );
		$partnerId = isset( $params['partner_id'] ) ? $params['partner_id'] : 0;
		$stepId    = isset( $params['step_id'] ) ? $params['step_id'] : 0;

		// Pagination parameters
		$start  = (int) ( $params['start'] ?? 0 );
		$length = (int) ( $params['length'] ?? 10 );
		$search = isset( $params['search'] ) && isset( $params['search']['value'] ) ? $params['search']['value'] : '';

		$projects      = $this->projectChildRepository->getAllProjectChildren( $search, $start, $length, $partnerId, $stepId );
		$filteredCount = $this->projectChildRepository->countAllFilteredProjectChildren( $search, $partnerId, $stepId );
		$totalCount    = $this->projectChildRepository->countAllProjectChildren( $partnerId, $stepId );
		$data          = [
			'draw'            => $draw,
			'recordsTotal'    => $totalCount,
			'recordsFiltered' => $filteredCount,
			'data'            => array_map( function ( $project ) {
				return $project;
			}, $projects ),
		];

		return new JsonResponse( $data );
	}

	#[Route( '/{id}/show', name: 'project_child_show', methods: [ 'GET' ] )]
	public function showProject( $id ): Response {
		$projectChild = $this->projectChildRepository->findOneBy( [ 'id' => $id ] );
		$subProjects  = $this->subProjectRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityId'   => $projectChild->getId()
		] );

		$galleries = $this->galleryRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityId'   => $projectChild->getId()
		] );

		$storyTelling = $this->storyTellingRepository->findBy( [
			'entityType' => ProjectChild::class,
			'entityID'   => $projectChild->getId()
		] );
		$parcels = $this->parcelRepository->findParcelByProject( $projectChild->getId() );

		return $this->render( 'admin/project-child/show.html.twig', [
			'project'      => $projectChild,
			'subProjects'  => $subProjects,
			'galleries'    => $galleries,
			'storyTelling' => $storyTelling,
			'parcels'      => $parcels
		] );
	}

    /**
     * @throws CustomException
     */
    #[Route( '/create', name: 'project_child_create', methods: [ 'GET', 'POST' ] )]
	public function create( Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader ): Response {
		$project = new ProjectChild();
		$form    = $this->createForm( ProjectChildType::class, $project );
		$form->handleRequest( $request );
		$files = $request->files->all();

		if ( $form->isSubmitted() && $form->isValid() ) {
            if ( array_key_exists('uploadFile', $files) && !empty( $files['uploadFile'] ) && $files['uploadFile']->isValid() ) {
                $bannerUrl = $this->serviceImage->uploadBannerImage( PathName::UPLOAD_BANNER_PROJECT_CHILD, $files );
                $project->setBannerImage( $bannerUrl );
            }
			$entityManager->persist( $project );
			$entityManager->flush();

			$this->addFlash( 'success', 'Création effectuée avec succès' );

			return $this->redirectToRoute( 'project_child_edit', [ 'id' => $project->getId() ], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'admin/project-child/create.html.twig', [
			'project' => $project,
			'form'    => $form,
		] );
	}

    /**
     * @throws CustomException
     */
    #[Route( '/{id}/edit', name: 'project_child_edit', methods: [ 'GET', 'POST' ] )]
	public function edit( Request $request, EntityManagerInterface $entityManager, ProjectChild $project ) {
		$edit_form = $this->createForm( ProjectChildType::class, $project );
		$edit_form->handleRequest( $request );
        $subProjects = $this->subProjectRepository->findBy( [
            'entityType' => ProjectChild::class,
            'entityId'   => $project->getId()
        ] );
        $galleries = $this->galleryRepository->findBy( [
            'entityType' => ProjectChild::class,
            'entityId'   => $project->getId()
        ] );
        $storyTelling = $this->storyTellingRepository->findBy( [
            'entityType' => ProjectChild::class,
            'entityID'   => $project->getId()
        ] );
        $parcels = $this->parcelRepository->findParcelByProject( $project->getId() );
		if ( $edit_form->isSubmitted() && $edit_form->isValid() ) {
			$files = $request->files->all();
			if ( array_key_exists('uploadFile', $files) && !empty( $files['uploadFile'] ) && $files['uploadFile']->isValid() ) {
				$bannerUrl = $this->serviceImage->uploadBannerImage( PathName::UPLOAD_BANNER_PROJECT_CHILD, $files );
				$project->setBannerImage( $bannerUrl );
			}
            $entityManager->persist($project);
            $entityManager->flush();

			$this->addFlash( 'success', 'Modification effectuée avec succès' );
		}
		return $this->render( 'admin/project-child/edit.html.twig', [
			'project' => $project,
			'form'    => $edit_form,
			'subProjects'  => $subProjects,
			'galleries'    => $galleries,
			'storyTelling' => $storyTelling,
			'parcels'      => $parcels
		]);
	}

	#[Route('/{id}/delete', name: 'project_child_delete', methods: ['DELETE'])]
	public function delete(int $id, EntityManagerInterface $entityManager): Response {
		$response = false;
		$message = 'Erreur lors de la suppression';
		$project = $this->projectChildRepository->findOneBy(['id' => $id]);

		if ($project) {
			// Retrieve and delete related Parcel entities
			$parcels = $this->parcelRepository->findBy(['projectChild' => $project]);
			foreach ($parcels as $parcel) {
				// Retrieve and delete related AllocatedTree entities
				$allocatedTrees = $entityManager->getRepository(AllocatedTree::class)->findBy(['parcel' => $parcel]);
				foreach ($allocatedTrees as $allocatedTree) {
					$entityManager->remove($allocatedTree);
				}
				$entityManager->remove($parcel);
			}

			// Delete the ProjectChild entity
			$entityManager->remove($project);
			$entityManager->flush();
			$response = true;
			$message = 'Suppression effectuée avec succès';
		}

		return $this->json([
			'success' => $response,
			'message' => $message
		]);
	}
}
