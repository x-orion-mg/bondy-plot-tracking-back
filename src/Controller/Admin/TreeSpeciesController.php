<?php

namespace App\Controller\Admin;

use App\Entity\StoryTelling;
use App\Entity\TreeSpecies;
use App\Entity\UsageType;
use App\Repository\StoryTellingRepository;
use App\Repository\GalleryRepository;
use App\Repository\TreeSpeciesRepository;
use App\Form\TreeSpeciesType;
use App\Service\ServiceUploadFiles;
use App\Utils\PathName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tree-species')]
class TreeSpeciesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TreeSpeciesRepository $TreeSpeciesRepository,
        private ServiceUploadFiles $serviceUploadFiles,
        private StoryTellingRepository $storyTellingRepository,
        private GalleryRepository $galleryRepository
    ){
        $this->entityManager         = $entityManager;
        $this->TreeSpeciesRepository = $TreeSpeciesRepository;
        $this->serviceUploadFiles    = $serviceUploadFiles;
        $this->storyTellingRepository = $storyTellingRepository;
        $this->galleryRepository      = $galleryRepository;
    }

    #[Route(name: 'tree_species_index', methods: ['GET'])]
    public function index(): Response
    {
        $treeSpecies = $this->entityManager
            ->getRepository(TreeSpecies::class)
            ->findAll();

        $usageTypes = $this->entityManager
            ->getRepository(UsageType::class)
            ->findAll();

        return $this->render('admin/tree-species/index.html.twig', [
            'treeSpecies' => $treeSpecies,
            'usageTypes'  => $usageTypes,
        ]);
    }

    #[Route('/{id}/edit', name: 'tree_species_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TreeSpecies $treeSpecy, int $id): Response
    {
        $galleries = $this->galleryRepository->findBy( [
			'entityType' => TreeSpecies::class,
			'entityId'   => $treeSpecy->getId()
		]);
		$storyTelling = $this->storyTellingRepository->findBy( [
			'entityType' => TreeSpecies::class,
			'entityID'   => $treeSpecy->getId()
		]);
        $treeSpecies = $this->entityManager
            ->getRepository(TreeSpecies::class)
            ->findAll();
        $usageTypes = $this->entityManager
            ->getRepository(UsageType::class)
            ->findAll();

        $form = $this->createForm(TreeSpeciesType::class, $treeSpecy);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $files  = $request->files->all();
            $banner = array_key_exists('uploadFile', $files) && !empty($files['uploadFile']) ? $files['uploadFile'] : '';
            $icon   = array_key_exists('iconFile', $files) && !empty($files['iconFile']) ? $files['iconFile'] : '';
            if ($banner && $banner->isValid()) {
                $fileUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_BANNER_TREE_SPECIES, $files);
                $treeSpecy->setBanner($fileUrl);
            }
            if ($icon && $icon->isValid()) {
                $fileUrl = $this->serviceUploadFiles->uploadIcon(PathName::UPLOAD_ICON_TREE_SPECIES, $files);
                $treeSpecy->setIcon($fileUrl);
            }
            $this->entityManager->flush();

            $this->addFlash( 'success', 'Modification effectuée avec succès' );
            return $this->redirectToRoute('tree_species_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/tree-species/edit.html.twig', [
            'form'         => $form->createView(),
            'treeSpecy'    => $treeSpecy,
            'galleries'    => $galleries,
            'storyTelling' => $storyTelling,
            'treeSpecies'  => $treeSpecies,
            'usageTypes'   => $usageTypes
        ]);
    }

    #[Route('/delete', name: 'tree_species_delete', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $response = false;
        $message  = 'Erreur lors de la suppression';
        $params   = $request->query->all();
        $id       = array_key_exists('id', $params) && !empty($params['id']) ? intval($params['id']) : 0;

        $treeSpecy = $this->TreeSpeciesRepository->findOneBy( ['id' => $id] );
        if ( $treeSpecy ) {
            $this->entityManager->remove($treeSpecy);
            $this->entityManager->flush();
            $response = true;
            $message = 'Suppression effectuée avec succès';
        }

        return $this->json([
            'success' => $response,
            'message' => $message
        ]);
    }

    #[Route('/new', name: 'tree_species_create', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $species = new TreeSpecies();
        $form    = $this->createForm( TreeSpeciesType::class, $species );
        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            $files = $request->files->all();
            $banner = array_key_exists('uploadFile', $files) && !empty($files['uploadFile']) ? $files['uploadFile'] : '';
            $icon   = array_key_exists('iconFile', $files) && !empty($files['iconFile']) ? $files['iconFile'] : '';
            if ($banner && $banner->isValid()) {
                $fileUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_BANNER_TREE_SPECIES, $files);
                $species->setBanner($fileUrl);
            }
            if ($icon && $icon->isValid()) {
                $fileUrl = $this->serviceUploadFiles->uploadIcon(PathName::UPLOAD_ICON_TREE_SPECIES, $files);
                $species->setIcon($fileUrl);
            }
	        $species->setTrackCode($this->generateTrackCode($species));
            $this->entityManager->persist( $species );
            $this->entityManager->flush();

            $this->addFlash( 'success', 'Création effectuée avec succès' );
            return $this->redirectToRoute('tree_species_edit', ['id' => $species->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/tree-species/create.html.twig', [
           'form'     => $form->createView(),
           'species' => $species,
        ]);
    }

	private function generateTrackCode(TreeSpecies $species): string
	{
		$commonName = strtoupper(substr($species->getCommonName(), 0, 5));
		$lastId = $this->TreeSpeciesRepository->findOneBy([], ['id' => 'DESC'])?->getId() ?? 0;
		$newId = $lastId + 1;
		$id = str_pad((string)$newId, 3, '0', STR_PAD_LEFT);
		return $commonName . '-B-' . $id;
	}
}
