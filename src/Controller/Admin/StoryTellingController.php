<?php

namespace App\Controller\Admin;

use App\Entity\StoryTelling;
use App\Repository\GalleryRepository;
use App\Repository\StoryTellingRepository;
use App\Service\FileUploader;
use App\Service\ServiceUploadFiles;
use App\Utils\PathName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route( '/admin/{entityType}/{entityId}/storytelling' )]
class StoryTellingController extends AbstractController {
	private EntityManagerInterface $entityManager;
	private Packages $packages;
	private RequestStack $requestStack;
	private GalleryRepository $galleryRepository;
	private StoryTellingRepository $storyTellingRepository;
    private ServiceUploadFiles $serviceUploadFiles;


	public function __construct(
		EntityManagerInterface $entityManager,
		Packages $packages,
		RequestStack $requestStack,
		GalleryRepository $galleryRepository,
		StoryTellingRepository $storyTellingRepository,
        ServiceUploadFiles $serviceUploadFiles
		) {
		$this->entityManager          = $entityManager;
		$this->requestStack           = $requestStack;
		$this->packages               = $packages;
		$this->galleryRepository      = $galleryRepository;
		$this->storyTellingRepository = $storyTellingRepository;
        $this->serviceUploadFiles     = $serviceUploadFiles;
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
			'project-child' => \App\Entity\ProjectChild::class,
			'parcel'        => \App\Entity\Parcel::class,
            'tree-species'  => \App\Entity\TreeSpecies::class,
		];

		return $entityClasses[ $entityType ] ?? '';
	}

	#[Route( '/', name: 'story_telling_index', methods: [ 'GET' ] )]
	public function index(  string $entityType, int $entityId ): Response {
		$storyTelling = $this->storyTellingRepository->findBy( [
			'entityType' => $entityType,
			'entityId'   => $entityId
		] );

		return $this->render( 'admin/story-telling/index.html.twig', [
			'storyTelling' => $storyTelling,
		] );
	}

	#[Route( '/new', name: 'story_telling_new', methods: [ 'GET', 'POST' ] )]
	public function new( Request $request, string $entityType, int $entityId, FileUploader $fileUploader ): Response {
		$storyTelling = new StoryTelling();

		if ( $request->isMethod( 'POST' ) && !empty($this->getEntityClass( $entityType )) ) {
			$data  = $request->request->all();
			$files = $request->files->all();

			$storyTelling->setEntityType( $this->getEntityClass( $entityType ) );
			$storyTelling->setEntityId( $entityId );
			$storyTelling->setTitle( $data['title'] ?? $storyTelling->getTitle() );
			$storyTelling->setDescription( $data['description'] ?? substr($storyTelling->getDescription(), 0, 300) ) ;
			$storyTelling->setContent( $data['content'] ?? $storyTelling->getContent());

			$request = $this->requestStack->getCurrentRequest();
			$baseUrl = $request->getSchemeAndHttpHost();
			if ( ! empty( $files['uploadFile'] ) && $files['uploadFile']->isValid() ) {
                $fileUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_BANNER_STORY_TELLING, $files);
                $storyTelling->setBannerImage($fileUrl);
			} else {
				// Set a default value or handle the case where no file is uploaded
				$storyTelling->setBannerImage( $baseUrl . $this->packages->getUrl( 'uploads/banners/default-banner.jpg' ) );
			}

			$this->entityManager->persist( $storyTelling );
			$this->entityManager->flush();

			$editUrl = $this->generateUrl( 'story_telling_edit', [
				'entityType' => $entityType,
				'entityId'   => $entityId,
				'id'         => $storyTelling->getId()
			] );

			return $this->json( [ 'success' => true, 'editUrl' => $editUrl ] );

		} elseif ( $request->isMethod( 'GET' ) ) {
			$gallery = $this->galleryRepository->findBy( [
				'entityType' => $this->getEntityClass( $entityType ),
				'entityId'   => $entityId
			] );

			return $this->render( 'admin/story-telling/new.html.twig', [
				'entityType'   => $entityType,
				'entityId'     => $entityId,
				'storyTelling' => $storyTelling,
				'gallery'      => $gallery,
			] );
		}

		return $this->json( [ 'success' => false, 'message' => 'Méthode de requête non valide.' ] );
	}

	#[Route( '/{id}/edit', name: 'story_telling_edit', methods: [ 'GET', 'POST' ] )]
	public function edit( Request $request, StoryTelling $storyTelling, string $entityType, int $entityId, FileUploader $fileUploader ): Response {

		if ( $request->isMethod( 'POST' ) ) {
			$data  = $request->request->all();
			$files = $request->files->all();

			$storyTelling->setEntityType( $this->getEntityClass( $entityType ) );
			$storyTelling->setEntityId( $entityId );
			$storyTelling->setTitle( $data['title'] ?? $storyTelling->getTitle() );
			$storyTelling->setDescription( $data['description'] ?? substr($storyTelling->getDescription(), 0, 300) );
			$storyTelling->setContent( $data['content'] ?? $storyTelling->getContent() );


			if ( isset( $files['uploadFile'] ) && $files['uploadFile']->isValid() ) {
                $fileUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_BANNER_STORY_TELLING, $files);
                $storyTelling->setBannerImage($fileUrl);
			}

			$this->entityManager->persist( $storyTelling );
			$this->entityManager->flush();

			return $this->json( [ 'success' => true ] );
		}

		if ( $request->isMethod( 'GET' )  ) {

			$galleries = $this->galleryRepository->findBy( [
				'entityType' => StoryTelling::class,
				'entityId'   => $storyTelling->getId()
			] );
			return $this->render( 'admin/story-telling/edit.html.twig', [
				'entityType'   => $entityType,
				'entityId'     => $entityId,
				'storyTelling' => $storyTelling,
				'galleries'    => $galleries,
			] );
		}

		return $this->json( [ 'success' => false, 'message' => 'Méthode de requête non valide.' ] );
	}

	private function findStoryTelling(int $id): ?StoryTelling {
		return $this->entityManager->getRepository(StoryTelling::class)->find($id);
	}

	#[Route( '/{id}/delete', name: 'story_telling_delete', methods: [ 'DELETE' ] )]
	public function delete(  int $id ): Response {
		$storyTelling = $this->findStoryTelling($id);

		if (!$storyTelling) {
			return $this->json(['success' => false, 'message' => 'Le Story telling n\'a pas été trouvée.'], Response::HTTP_NOT_FOUND);
		}
		$this->entityManager->remove( $storyTelling );
		$this->entityManager->flush();

		return $this->json( [ 'success' => true ] );
	}
}
