<?php

namespace App\Service;

use ZipArchive;
use Lraveri\GeojsonConverter\Exception\InvalidXmlException;

class KmzConverter
{
    private string $kmlContent;

    public function loadKmz(string $filePath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (pathinfo($stat['name'], PATHINFO_EXTENSION) === 'kml') {
                    $this->kmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
            $zip->close();
        } else {
            throw new \RuntimeException("Unable to open KMZ file.");
        }

        if (empty($this->kmlContent)) {
            throw new \RuntimeException("No KML file found in the KMZ archive.");
        }
    }

    public function convertToGeoJson(): string
    {
        $xml = simplexml_load_string($this->kmlContent);
        if ($xml === false) {
            throw new InvalidXmlException("Error parsing the KML file.");
        }

        $features = $placemarks = [];
        if (
            isset($xml->Document) &&
            isset($xml->Document->Folder) &&
            count($xml->Document->Folder) > 0 &&
            isset($xml->Document->Folder->Placemark) &&
            count($xml->Document->Folder->Placemark) > 0
        ) {
            $placemarks = $xml->Document->Folder->Placemark;
        }

        if (!empty($placemarks)) {
            foreach ($placemarks as $placemark) {
                $features[] = $this->parsePlacemark($placemark);
            }
        }

        return json_encode($features && !empty($features) &&  isset($features[0]) && !empty($features[0]) ? $features[0] : [], JSON_PRETTY_PRINT);
    }

    private function parsePlacemark($placemark): array
    {
        $geometry = $this->parseGeometry($placemark);

        $properties = [
            'name'        => (string) $placemark->name,
            'description' => (string) $placemark->description,
        ];

        return [
            'id'         => (intval($placemark->id)),
            'type'       => 'Feature',
            'properties' => $properties,
            'geometry'   => $geometry
        ];
    }

    private function parseGeometry($placemark): array
    {
        if (isset($placemark->Point)) {
            $coordinates = explode(',', trim((string) $placemark->Point->coordinates));
            return [
                'type'        => 'Point',
                'coordinates' => array_map('floatval', $coordinates)
            ];
        }

        if (isset($placemark->LineString)) {
            $coordinatesList = explode(' ', trim((string) $placemark->LineString->coordinates));
            $coordinates = array_map(function ($coord) {
                $parts = explode(',', $coord);
                return array_map('floatval', $parts);
            }, $coordinatesList);

            return [
                'type'        => 'LineString',
                'coordinates' => $coordinates
            ];
        }

        if (isset($placemark->MultiGeometry) && isset($placemark->MultiGeometry->Polygon)) {
            $coordinatesList = explode(' ', trim((string) $placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates));
            $coordinates = array_map(function ($coord) {
                $parts = explode(',', $coord);
                return array_map('floatval', $parts);
            }, $coordinatesList);

            return [
                'type'        => 'Polygon',
                'coordinates' => [$coordinates]
            ];
        }

        return [];
    }
}
