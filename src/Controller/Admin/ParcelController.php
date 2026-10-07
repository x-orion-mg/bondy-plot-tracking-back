<?php

namespace App\Controller\Admin;

use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\StoryTelling;
use App\Repository\ParcelRepository;
use App\Repository\ParcelTreeSpeciesRepository;
use App\Repository\TreeSpeciesRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\GalleryRepository;
use App\Service\ImageDroneService;
use App\Utils\Utils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\ServiceUploadFiles;
use App\Utils\PathName;
use App\Service\GpxConverter;
use App\Service\KmlConverter;
use App\Service\KmzConverter;
use App\Service\GeoJsonService;
use App\Message\ProcessImageDrone;

#[Route('/admin/parcel')]
final class ParcelController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ParcelRepository $parcelRepository;
    private TreeSpeciesRepository $treeSpeciesRepository;
    private ParcelTreeSpeciesRepository $parcelTreeSpeciesRepository;
    private ProjectChildRepository $projectChildRepository;
    private ServiceUploadFiles $serviceUploadFiles;

    public function __construct(
        EntityManagerInterface      $entityManager,
        ParcelRepository            $parcelRepository,
        TreeSpeciesRepository       $treeSpeciesRepository,
        ParcelTreeSpeciesRepository $parcelTreeSpeciesRepository,
        ProjectChildRepository      $projectChildRepository,
        ServiceUploadFiles          $serviceUploadFiles,
        MessageBusInterface         $messageBus
    )
    {
        $this->entityManager = $entityManager;
        $this->parcelRepository = $parcelRepository;
        $this->treeSpeciesRepository = $treeSpeciesRepository;
        $this->parcelTreeSpeciesRepository = $parcelTreeSpeciesRepository;
        $this->projectChildRepository = $projectChildRepository;
        $this->serviceUploadFiles = $serviceUploadFiles;
        $this->messageBus = $messageBus;
    }

    #[Route('/create', name: 'parcel_create', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $params = $request->query->all();
        $projectId = array_key_exists('entityId', $params) && !empty($params['entityId']) ? $params['entityId'] : 0;
        $project = $this->projectChildRepository->findProjectChildById($projectId);

        return $this->render('admin/parcel/create.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'parcel_edit', methods: ['GET', 'POST'])]
    public function edit(Parcel $parcel, GalleryRepository $galleryRepository): Response
    {
        $projectId = $parcel->getProjectChild()->getId() ?? 0;
        $project = $this->projectChildRepository->findProjectChildById($projectId);
        $galleries = $galleryRepository->findBy([
            'entityType' => Parcel::class,
            'entityId' => $parcel->getId()
        ]);
        $storyTelling = $this->entityManager->getRepository(StoryTelling::class)->findBy([
            'entityType' => Parcel::class,
            'entityID' => $parcel->getId()
        ]);
        $treeSpecies = $this->treeSpeciesRepository->getAllTreeSpecies();
        $parcelTreeSpecies = $this->parcelTreeSpeciesRepository->getTreeSpeciesByParcel($parcel->getId());

        return $this->render('admin/parcel/edit.html.twig', [
            'parcel' => $parcel,
            'project' => $project,
            'galleries' => $galleries,
            'storyTelling' => $storyTelling,
            'treeSpecies' => $treeSpecies,
            'parcelTreeSpecies' => $parcelTreeSpecies
        ]);
    }

    #[Route('/delete', name: 'delete_parcel', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $response = false;
        $message = 'Erreur lors de la suppression';
        $data = $request->request->all();
        $parcelId = isset($data['id']) ? intval($data['id']) : 0;
        $parcel = $this->parcelRepository->find($parcelId);

        if ($parcelId > 0 && $parcel) {
            $this->entityManager->remove($parcel);
            $this->entityManager->flush();

            $response = true;
            $message = 'Suppression effectuée avec succès';
        }

        return $this->json([
            'success' => $response,
            'message' => $message
        ]);
    }

    #[Route('/generate-tree', name: 'generate_tree_parcel', methods: ['POST'])]
    public function generateTreeParcel(Request $request, GeoJsonService $geoJsonService): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = false;
        $message = 'Erreur lors de la génération des arbres';

        $params = $request->request->all();
        $parcelId = $this->getParcelId($params);
        $treesData = $this->getTreesData($params);

        if ($parcelId > 0 && !empty($treesData)) {
            $treeNumber = $this->getTotalTreeNumber($treesData);
            $parcel = $this->parcelRepository->find($parcelId);
            if (!empty($parcel->getDelimitation())) {
                $utils = new Utils();
                $trackCode = $utils->generateTrackCode($parcel);
                $treeCoordinates = $geoJsonService->generateTreesGeoJson($parcel->getDelimitation(), $treeNumber);
                $arrayTreeCoordinates = json_decode($treeCoordinates, true);

                if ($treeCoordinates && !array_key_exists('error', $arrayTreeCoordinates)) {
                    $newGeoJson = $this->processTreeCoordinates($treeCoordinates, $treesData, $trackCode);

                    $parcel->setTrees(json_encode($newGeoJson));
                    $this->entityManager->persist($parcel);
                    $this->entityManager->flush();

                    $this->parcelTreeSpeciesRepository->saveParcelTreesSpecies($treesData, $parcel);

                    $message = 'Génération des arbres effectuée avec succès';
                    $response = true;
                }
            }
        }

        return $this->json([
            'success' => $response,
            'message' => $message
        ]);
    }

    private function getParcelId(array $params): int
    {
        return isset($params['parcelId']) ? intval($params['parcelId']) : 0;
    }

    private function getTreesData(array $params): array
    {
        return isset($params['treesData']) ? json_decode($params['treesData'], true) : [];
    }

    private function getTotalTreeNumber(array $treesData): int
    {
        return array_sum(array_column($treesData, "number"));
    }

    /**
     * @param string $treeCoordinates
     * @param array $treesData
     * @param string $trackCode
     * @return array
     */
    private function processTreeCoordinates(string $treeCoordinates, array $treesData, string $trackCode): array
    {
        $geoJsonFeatures = json_decode($treeCoordinates, true);
        $newGeoJson = $species = [];
        $idCounter = 1;
        if ($geoJsonFeatures && !array_key_exists('error', $geoJsonFeatures)) {
            foreach ($treesData as $tree) {
                $species = $this->treeSpeciesRepository->find($tree["id"]) ?? array();
                for ($i = 0; $i < $tree["number"]; $i++) {
                    $feature = array_shift($geoJsonFeatures);
                    $formattedIdCounter = sprintf('%05d', $idCounter);
                    $feature = $this->updateFeature($feature, $trackCode . $formattedIdCounter, $species);
                    $newGeoJson[] = $feature;
                    $idCounter++;
                }
            }

            // Ajouter les arbres restants
            foreach ($geoJsonFeatures as $feature) {
                $formattedIdCounter = sprintf('%05d', $idCounter);
                $feature = $this->updateFeature($feature, $trackCode . $formattedIdCounter, $species);
                $newGeoJson[] = $feature;
                $idCounter++;
            }
        }

        return $newGeoJson;
    }

    /**
     * @param $feature
     * @param string $idCounter
     * @param $species
     *
     * @return mixed
     */
    private function updateFeature($feature, string $idCounter, $species): mixed
    {
        $dataSpecies = array();
        if ($species) {
            $dataSpecies['id'] = $species->getId();
            $dataSpecies['commonName'] = $species->getCommonName();
            $dataSpecies['scientificName'] = $species->getScientificName();
            $dataSpecies['trackCode'] = $species->getTrackCode();
            $dataSpecies['banner'] = $species->getBanner();
            $dataSpecies['icon'] = $species->getIcon();
            if ($species->getUsageType()) {
                $dataSpecies['usageType']['id'] = $species->getUsageType()->getId();
                $dataSpecies['usageType']['label'] = $species->getUsageType()->getLabel();
                $dataSpecies['usageType']['icon'] = $species->getUsageType()->getIcon();
            }
        }

        if (is_object($feature)) {
            $feature->id = $idCounter;
            $feature->species = is_object($dataSpecies) ? get_object_vars($dataSpecies) : $dataSpecies;
        } else {
            $feature["id"] = $idCounter;
            $feature["species"] = is_object($dataSpecies) ? get_object_vars($dataSpecies) : $dataSpecies;
        }

        return $feature;
    }

    #[Route('/create-parcel', name: 'create_parcel_ajax', methods: ['POST'])]
    public function createParcel(Request $request, KmzConverter $kmzConverter, KmlConverter $kmlConverterService): JsonResponse
    {
        $params = $request->request->all();
        $files = $request->files->all();

        $projectId = array_key_exists('projectId', $params) ? intval($params['projectId']) : 0;
        $projectChild = $this->projectChildRepository->find($projectId);
        if (!$projectChild) {
            $response = false;
            $message = 'Projet du partenaire introuvable';
        } else {
            $parcel = new Parcel();
            $this->updateParcelDetails($parcel, $params);
            $parcel->setProjectChild($projectChild);
            $this->handleBoundaryFile($files, $parcel, $kmzConverter, $kmlConverterService);
            $this->handleBannerImage($files, $parcel);

            $this->entityManager->persist($parcel);
            $this->entityManager->flush();

            $response = true;
            $message = 'Création effectuée avec succès';
        }

        return $this->json([
            'success' => $response,
            'message' => $message,
            'parcelId' => $parcel->getId(),
        ]);
    }

    #[Route('/{id}/edit-parcel', name: 'edit_parcel_ajax', methods: ['POST'])]
    public function editParcel(Request $request, int $id, KmzConverter $kmzConverter, KmlConverter $kmlConverterService): JsonResponse
    {
        $params = $request->request->all();
        $files = $request->files->all();

        $parcel = $this->parcelRepository->find($id);
        if (!$parcel) {
            return $this->json([
                'success' => false,
                'message' => 'Parcels introuvables'
            ]);
        }

        $this->handleBannerImage($files, $parcel);
        $this->handleBoundaryFile($files, $parcel, $kmzConverter, $kmlConverterService);

        $this->updateParcelDetails($parcel, $params);

        $this->entityManager->persist($parcel);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Modification effectuée avec succès'
        ]);
    }

    private function handleImageDrone(array $files, Parcel $parcel): void
    {
        $imageDrone = array_key_exists('droneImage', $files) ? $files['droneImage'] : null;
        if (!empty($imageDrone)) {
            $this->messageBus->dispatch(new ProcessImageDrone(
                $imageDrone->getPathname(),
                PathName::UPLOAD_IMG_DRONE_PARCEL . '/' . $parcel->getId(),
                $imageDrone->getClientOriginalName(),
                $parcel->getId()
            ));
        }
    }

    private function handleBannerImage(array $files, Parcel $parcel): void
    {
        $bannerImage = array_key_exists('uploadFile', $files) ? $files['uploadFile'] : null;
        if (!empty($bannerImage)) {
            $bannerImageUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_BANNER_PARCELS, $files);
            $parcel->setBannerImage($bannerImageUrl);
        }
    }

    private function handleBoundaryFile(array $files, Parcel $parcel, KmzConverter $kmzConverter, KmlConverter $kmlConverterService): void
    {
        $boundary = array_key_exists('boundaryFile', $files) && !empty($files['boundaryFile']) ? $files['boundaryFile'] : null;
        if (!empty($boundary)) {
            $filePath = $boundary->getPathname();
            $extension = $boundary->getClientOriginalExtension();

            $data = $this->convertBoundaryFile($extension, $filePath, $kmzConverter, $kmlConverterService);

            $geoJson = json_decode($data);
            $geoJson->id = $parcel->getId();

            if (is_array($geoJson->properties)) {
                $geoJson->properties = (object)$geoJson->properties;
            } elseif (!isset($geoJson->properties)) {
                $geoJson->properties = new \stdClass();
            }

            $geoJson->properties->title = $parcel->getTitle();
            $parcel->setDelimitation(json_encode($geoJson));
        }
    }

    private function convertBoundaryFile(string $extension, string $filePath, KmzConverter $kmzConverter, KmlConverter $kmlConverterService): string
    {
        $data = '';
        if (in_array($extension, ['kml', 'kmz'])) {
            if ($extension === 'kmz') {
                $kmzConverter->loadKmz($filePath);
                $data = $kmzConverter->convertToGeoJson();
            } else {
                $fileContent = file_get_contents($filePath);
                $kmlConverterService->setFileContent($fileContent);
                $data = $kmlConverterService->convert();
            }
        } elseif ($extension === 'gpx') {
            $fileContent = file_get_contents($filePath);
            $gpxConverter = new GpxConverter($fileContent);
            $data = $gpxConverter->convert();
        }
        return $data;
    }

    private function updateParcelDetails(Parcel $parcel, array $params): void
    {
        $parcel->setTitle($params['title']);
        $parcel->setCity($params['city']);
        $parcel->setLatitude($params['latitude'] ? (float)str_replace(',', '.', $params['latitude']) : 0);
        $parcel->setLongitude($params['longitude'] ? (float)str_replace(',', '.', $params['longitude']) : 0);
        $parcel->setStartDate(
            !empty($params['startDate'])
                ? DateTime::createFromFormat('d/m/Y', $params['startDate'])
                : new DateTime()
        );
        $parcel->setPlantingYear(
            !empty($params['plantingYear'])
                ? DateTime::createFromFormat('Y', $params['plantingYear'])
                : new DateTime()
        );
    }


    #[Route('/insert-image-drone/{parcelId}', name: 'insert_image_drone_parcel', methods: ['POST'])]
    public function insertImageDrone(Request $request, int $parcelId): JsonResponse
    {
        $response = false;
        $message = 'Erreur lors de l\'insertion de l\'image drone';
        $parcel = $this->parcelRepository->find($parcelId);
        if (!$parcel) {
            return new JsonResponse([
                'success' => $response,
                'message' => 'Parcelle introuvable'
            ], Response::HTTP_NOT_FOUND);
        }

        $files = $request->files->all();
        $imageDrone = array_key_exists('droneImage', $files) ? $files['droneImage'] : null;
        if (!empty($imageDrone)) {
            $this->handleImageDrone($files, $parcel);

            $response = true;
            $message = 'Insertion effectuée avec succès';
        }

        return new JsonResponse([
            'success' => $response,
            'message' => $message
        ], Response::HTTP_OK);
    }

}
