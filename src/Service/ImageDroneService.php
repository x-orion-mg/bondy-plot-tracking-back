<?php

namespace App\Service;

use Imagick;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Json;

class ImageDroneService
{
    public function __construct(
        private RequestStack $requestStack,
        private Packages $packages)
    {
        $this->requestStack = $requestStack;
        $this->packages     = $packages;
    }

    public function convertToPng(string $tiffPath, string $outputDir, string $originalFileName): string
    {
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            return '';
        }

        $image = new Imagick($tiffPath);
        $image->setImageFormat('png');

        $outputPath = $outputDir . '/' . pathinfo($originalFileName, PATHINFO_FILENAME) . '.png';
        if (!$image->writeImage($outputPath)) {
            throw new \Exception("Failed to write image to: " . $outputPath);
        }

        $image->clear();
        $image->destroy();

        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request->getSchemeAndHttpHost();
        $outputPath = $baseUrl . $this->packages->getUrl($outputPath);

        return $outputPath;
    }

    public function getMetadata(string $filePath, string $outputDir, string $originalFileName): string
    {
        $command = "gdalinfo -json " . escapeshellarg($filePath);
        $output = shell_exec($command);
        $metadata = json_decode($output, true);

        $bounds = array();
        $coords = $metadata['wgs84Extent']['coordinates'][0] ?? null;
        if ($metadata['wgs84Extent'] && $metadata['wgs84Extent']['coordinates'] && $metadata['wgs84Extent']['coordinates'][0]) {
            $minLon = $maxLon = $coords[0][0];
            $minLat = $maxLat = $coords[0][1];

            foreach ($coords as [$lon, $lat]) {
                if ($lon < $minLon) $minLon = $lon;
                if ($lon > $maxLon) $maxLon = $lon;
                if ($lat < $minLat) $minLat = $lat;
                if ($lat > $maxLat) $maxLat = $lat;
            }

            $bounds = [$minLon, $minLat, $maxLon, $maxLat];
        }

        return json_encode($bounds);
    }

    public function compressToTiles(string $tiffPath, string $outputDir, string $zoomLevels = '0-5'): string
    {
        // Ensure the output directory exists
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            throw new \Exception("Failed to create output directory: " . $outputDir);
        }

        // Build the gdal2tiles command
        $command = sprintf(
            'gdal2tiles.py -z %s -w none %s %s',
            escapeshellarg($zoomLevels),
            escapeshellarg($tiffPath),
            escapeshellarg($outputDir)
        );

        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        // Check for errors
        if ($returnCode !== 0) {
            throw new \Exception("Failed to generate tiles. Command output: " . implode("\n", $output));
        }

        // Generate the URL pattern
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request->getSchemeAndHttpHost();
        $relativePath = $this->packages->getUrl($outputDir);

        return $baseUrl . $relativePath . '/{z}/{x}/{y}.png';
    }

    public function generateThumbnail(string $filePath, string $thumbnailPath): void
    {
        $image = new \Imagick($filePath);

        // Set the size for the thumbnail (e.g., 200x200 pixels)
        $image->thumbnailImage(200, 200, true);

        // Save the thumbnail to the specified path
        if (!$image->writeImage($thumbnailPath)) {
            throw new \Exception("Failed to write thumbnail to: " . $thumbnailPath);
        }

        $image->clear();
        $image->destroy();
    }

    public function compressImage(string $imagePath, int $maxFileSize): void
    {
        $image = new \Imagick($imagePath);

        // Reduce quality iteratively until the file size is below the limit
        $quality = 90;
        while ($image->getImageLength() > $maxFileSize && $quality > 10) {
            $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $image->setImageCompressionQuality($quality);
            $image->stripImage(); // Remove metadata to reduce size
            $image->writeImage($imagePath);
            $quality -= 10;
        }

        if ($image->getImageLength() > $maxFileSize) {
            throw new \Exception("Unable to compress image below " . $maxFileSize . " bytes.");
        }

        $image->clear();
        $image->destroy();
    }
}
