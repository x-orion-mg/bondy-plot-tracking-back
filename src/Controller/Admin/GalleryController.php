<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use App\Entity\Parcel;
use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\StoryTelling;
use App\Entity\SubProject;
use App\Entity\TreeSpecies;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/admin/{entityType}/{entityId}/galleries' )]
class GalleryController extends AbstractController {
	private EntityManagerInterface $entityManager;

	private array $strategies;

	public function __construct( EntityManagerInterface $entityManager, array $strategies ) {
		$this->entityManager = $entityManager;
		$this->strategies = $strategies;
	}

	private function getEntityClass( string $entityType ): string {
		$entityClasses = [
			'project-parent' => ProjectParent::class,
			'project-child'  => ProjectChild::class,
			'sub-project'    => SubProject::class,
			'parcel'         => Parcel::class,
			'story-telling'  => StoryTelling::class,
            'tree-species'   => TreeSpecies::class,
			// Add other entity types if needed
		];

		return $entityClasses[ $entityType ] ?? '';
	}


	#[Route( '/new', name: 'gallery_new', methods: [ 'GET', 'POST' ] )]
	public function new( Request $request, string $entityType, int $entityId, FileUploader $fileUploader ): Response {
		$gallery = new Gallery();
		if ( $request->isMethod( 'POST' ) ) {
			$data  = $request->request->all();
			$files = $request->files->all();

			$gallery->setEntityType( $this->getEntityClass( $entityType ) );
			$gallery->setEntityId( $entityId );
			$this->extracted( $gallery, $data, $files, $request, $fileUploader );

			$this->entityManager->persist( $gallery );
			$this->entityManager->flush();

			return $this->json( [ 'success' => true ] );
		}

		return $this->json( [ 'success' => false, 'message' => 'Méthode de requête non valide.' ] );
	}


	#[Route( '/{id}/edit', name: 'gallery_edit', methods: [ 'GET', 'POST' ] )]
	public function edit( Request $request, Gallery $gallery, string $entityType, int $entityId, FileUploader $fileUploader ): Response {
		if ( $request->isMethod( 'POST' ) ) {
			$data  = $request->request->all();
			$files = $request->files->all();

			$this->extracted( $gallery, $data, $files, $request, $fileUploader );

			$this->entityManager->flush();

			return $this->json( [ 'success' => true ] );
		}

		return $this->json( [ 'success' => false, 'message' => 'Méthode de requête non valide.' ] );
	}

	#[Route( '/{id}/delete', name: 'gallery_delete', methods: [ 'DELETE' ] )]
	public function delete( Request $request, Gallery $gallery, string $entityType, int $entityId ): Response {
		$this->entityManager->remove( $gallery );
		$this->entityManager->flush();

		return $this->json( [ 'success' => true ] );
	}

	/**
	 * @param Gallery $gallery
	 * @param array $data
	 * @param array $files
	 * @param Request|null $request
	 *
	 * @return void
	 */
	public function extracted(Gallery $gallery, array $data, array $files, ?Request $request): void {

		$gallery->setCaption($data['caption'] ?? '');
		if (!empty($files['uploadFile']) && $files['uploadFile']->isValid()) {
			$file = $files['uploadFile'];
			$mimeType = $file->getMimeType();

			foreach ($this->strategies as $strategy) {
				if ($strategy->supports($mimeType)) {
					$strategy->process($gallery, $file);
					break;
				}
			}
		}
	}
}
