<?php

namespace App\Service;

use Lraveri\GeojsonConverter\Exception\InvalidXmlException;
use Lraveri\GeojsonConverter\GeojsonConverterInterface;

class KmlConverter implements GeojsonConverterInterface
{
    private string $fileContent;

    public function setFileContent(string $fileContent): void
    {
        $this->fileContent = $fileContent;
    }

    public function convert(): string
    {
        if (empty($this->fileContent)) {
            throw new \LogicException("File content must be set before calling convert().");
        }

        $xml = simplexml_load_string($this->fileContent);
        if ($xml === false) {
            throw new InvalidXmlException("Error parsing the KML file.");
        }

        $coordinates = $placemarks = [];
        if (isset($xml->Document) &&
            isset($xml->Document->Folder) &&
            count($xml->Document->Folder) > 0 &&
            isset($xml->Document->Folder->Placemark) &&
            count($xml->Document->Folder->Placemark) > 0 &&
            isset($xml->Document->Folder->Placemark->MultiGeometry) &&
            count($xml->Document->Folder->Placemark->MultiGeometry) > 0 ) 
        {
            $placemarks = $xml->Document->Folder->Placemark->MultiGeometry;
        }
        if ( !empty($placemarks) ) {
            foreach ($placemarks as $placemark) {
                if (isset($placemark->Polygon)) {
                    $coordinates = $this->parsePolygon($placemark->Polygon);
                    break; // On prend le premier Polygone trouvé
                }
            }
        }

        // Construire la structure GeoJSON demandée
        $geojson = [
            "id"   => "",
            "type" => "Feature",
            "properties" => [
                "title"  => ""
            ],
            "geometry" => [
                "type" => "Polygon",
                "coordinates" => [$coordinates] // Un seul tableau pour englober les points
            ]
        ];

        return json_encode($geojson, JSON_PRETTY_PRINT);
    }

    protected function parsePolygon($polygon): array
    {
        $coordinates = [];
        
        if (isset($polygon->outerBoundaryIs->LinearRing->coordinates)) {
            $coordinatesList = trim((string) $polygon->outerBoundaryIs->LinearRing->coordinates);
            $coordinates = array_map(function($coord) {
                $parts = explode(',', trim($coord));
                return [(float) $parts[0], (float) $parts[1]]; // Ignorer l'altitude
            }, explode(' ', $coordinatesList));
        }

        // Fermer le polygone si nécessaire
        if (!empty($coordinates) && $coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }

        return $coordinates;
    }
}
