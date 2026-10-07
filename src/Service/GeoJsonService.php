<?php

namespace App\Service;

use Psr\Log\LoggerInterface;	
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GeoJsonService {

	private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

	public function generateTreesGeoJson( string $geojsonContent, int $numTrees = 100 ): string {
		// Décoder le GeoJSON
		$geojson     = json_decode( $geojsonContent, true );
		$coordinates = array();
		if ( $geojson && array_key_exists('geometry', $geojson) && array_key_exists('coordinates', $geojson['geometry']) && !empty($geojson['geometry']['coordinates'][0]) ) {
			$coordinates = $geojson['geometry']['coordinates'][0];
			//$geojson['features'][0]['geometry']['coordinates'][0];
		} 
		if (!$coordinates) {
            $this->logger->error('Invalid GeoJSON format: missing coordinates.');
            return json_encode(['error' => 'Invalid GeoJSON format.'], JSON_PRETTY_PRINT);
        }
		
		// Générer les arbres
		$treeCoordinates = $this->generateTrees( $coordinates, $numTrees );

		// Créer un nouveau GeoJSON avec le polygone et les arbres
		$newGeojson = array();
		if ( $treeCoordinates ){
			$newGeojson = array_map( fn( $tree ) => [
				"coordinates" => $tree
			], $treeCoordinates);
		}
		
		return json_encode( $newGeojson, JSON_PRETTY_PRINT );
	}

	private function generateTrees(array $polygonCoordinates, int $numTrees): array {
		if (count($polygonCoordinates) < 3) {
            $this->logger->error('Not enough coordinates to form a polygon.');
            return [];
        }

		// Trier les coordonnées par latitude (axe Y)
		usort($polygonCoordinates, fn($a, $b) => $a[1] <=> $b[1]);
	
		// Déterminer la boîte englobante du polygone
		$minX = min(array_column($polygonCoordinates, 0));
		$maxX = max(array_column($polygonCoordinates, 0));
		$minY = min(array_column($polygonCoordinates, 1));
		$maxY = max(array_column($polygonCoordinates, 1));
	
		$width = $maxX - $minX;
		$height = $maxY - $minY;
		
		// Éviter les divisions par zéro
		if ($width == 0 || $height == 0) {
            $this->logger->error('Polygon dimensions are invalid.');
            return [];
        }
	
		// Déterminer le nombre de divisions de la grille
		$gridSizeX = max(1, (int) sqrt($numTrees * ($width / $height)));
		$gridSizeY = max(1, (int) ($numTrees / $gridSizeX));
	
		// Générer une grille de points à l'intérieur du polygone
		$treeGrid = [];
		$attempts = 0; // Limite pour éviter une boucle infinie
		$maxAttempts = $numTrees * 10;
	
		while (count($treeGrid) < $numTrees && $attempts < $maxAttempts) {
			$x = $minX + mt_rand() / mt_getrandmax() * $width;
			$y = $minY + mt_rand() / mt_getrandmax() * $height;
	
			if ($this->isPointInPolygon([$x, $y], $polygonCoordinates)) {
				$treeGrid[] = [$x, $y];
			}
	
			$attempts++;
		}
	
		return $treeGrid;
	}
	
	/**
	 * Vérifie si un point est à l'intérieur d'un polygone en utilisant l'algorithme du rayon.
	 */
	private function isPointInPolygon(array $point, array $polygon): bool {
		$x = $point[0];
		$y = $point[1];
		$inside = false;
		$n = count($polygon);
		
		for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
			$xi = $polygon[$i][0];
			$yi = $polygon[$i][1];
			$xj = $polygon[$j][0];
			$yj = $polygon[$j][1];
	
			$intersect = (($yi > $y) !== ($yj > $y)) &&
						 ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
			if ($intersect) {
				$inside = !$inside;
			}
		}
	
		return $inside;
	}

	public function saveGeoJsonFile(string $geoJsonContent, string $parcelTitle, int $parcelId, string $directory): string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $safeParcelName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $parcelTitle);
        $fileName = sprintf('%s_%d.geojson', $safeParcelName, $parcelId);
        $filePath = $directory . DIRECTORY_SEPARATOR . 'PARCEL_' . $parcelId . '_' . $fileName;

        file_put_contents($filePath, $geoJsonContent);

        return $filePath;
    }
	
}
