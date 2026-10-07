<?php

// src/MessageHandler/ProcessImageDroneHandler.php
namespace App\MessageHandler;

use App\Message\ProcessImageDrone;
use App\Repository\ParcelRepository;
use App\Service\ImageDroneService;
use App\Utils\PathName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProcessImageDroneHandler implements MessageHandlerInterface
{
    private ImageDroneService $imageDroneService;
    private ParcelRepository $parcelRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ImageDroneService $imageDroneService,
        ParcelRepository $parcelRepository,
        EntityManagerInterface $entityManager,
        private RequestStack $requestStack,)
    {
        $this->imageDroneService = $imageDroneService;
        $this->parcelRepository = $parcelRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function __invoke(ProcessImageDrone $message)
    {
        $coord = $this->imageDroneService->getMetadata($message->getFilePath(), $message->getOutputDir(), $message->getOriginalFileName());

        // Use the already injected $this->imageDroneService
        $parcel    = $this->parcelRepository->find($message->getParcelId());
        $directory = PathName::UPLOAD_IMG_DRONE_PARCEL . '/' . $parcel->getId();

        if (is_dir($directory)) {
            $filesystem = new Filesystem();
            $filesystem->remove($directory);
        }
        $tilesDir = $this->imageDroneService->compressToTiles(
            $message->getFilePath(),
            $message->getOutputDir(),
            '12-20'
        );
        // Generate and compress the thumbnail
        $thumbnailPath = $this->createThumbnail($message->getFilePath(), $message->getOutputDir());
        if ($parcel) {
            $request = $this->requestStack->getCurrentRequest();
            $baseUrl = $request->getSchemeAndHttpHost();

            $parcel->setTiffUrl($tilesDir);
            $parcel->setTiffCoordinates(json_decode($coord, true));
            $parcel->setTiffImage($baseUrl .'/'. $thumbnailPath);
            $this->entityManager->persist($parcel);
            $this->entityManager->flush();
        }
    }

    private function createThumbnail(string $filePath, string $outputDir): string
    {
        $thumbnailPath = $outputDir . '/thumbnail.jpg';

        // Use the ImageDroneService to create a thumbnail
        $this->imageDroneService->generateThumbnail($filePath, $thumbnailPath);

        // Compress the thumbnail to ensure it is under 1MB
        $this->imageDroneService->compressImage($thumbnailPath, 1 * 1024 * 1024); // 1MB

        return $thumbnailPath;
    }
}
