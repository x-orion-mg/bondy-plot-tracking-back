<?php

namespace App\Service;

use Lraveri\GeojsonConverter\Exception\InvalidXmlException;
use Lraveri\GeojsonConverter\GeojsonConverterInterface;

class GpxConverter implements GeojsonConverterInterface
{
    protected $fileContent;

    public function __construct(string $fileContent) {
        $this->fileContent = $fileContent;
    }

    public function convert(): string
    {
        $xml = simplexml_load_string($this->fileContent);
        if ($xml === false) {
            throw new InvalidXmlException("Error parsing the GPX file.");
        }

        $coordinates = [];

        // On récupère les points d'un track ou d'une route
        if (isset($xml->trk)) {
            $coordinates = $this->parseTrackAsPolygon($xml->trk);
        } elseif (isset($xml->rte)) {
            $coordinates = $this->parseRouteAsPolygon($xml->rte);
        }

        // Si pas de coordonnées, retour erreur
        if (empty($coordinates)) {
            throw new \RuntimeException("No valid track or route found in GPX.");
        }

        // Structure de sortie
        $geojson = [
            "id"   => "",
            "type" => "Feature",
            "properties" => [
            ],
            "geometry" => [
                "type" => "Polygon",
                "coordinates" => [$coordinates] // Un seul tableau englobant les points
            ]
        ];

        return json_encode($geojson, JSON_PRETTY_PRINT);
    }

    protected function parseTrackAsPolygon($tracks): array {
        $coordinates = [];
        foreach ($tracks as $trk) {
            foreach ($trk->trkseg as $trkseg) {
                foreach ($trkseg->trkpt as $trkpt) {
                    $coordinates[] = [(float) $trkpt['lon'], (float) $trkpt['lat']];
                }
            }
        }
        // Fermer le polygone si nécessaire
        if (!empty($coordinates) && $coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }
        return $coordinates;
    }

    protected function parseRouteAsPolygon($routes): array {
        $coordinates = [];
        foreach ($routes as $rte) {
            foreach ($rte->rtept as $rtept) {
                $coordinates[] = [(float) $rtept['lon'], (float) $rtept['lat']];
            }
        }
        // Fermer le polygone si nécessaire
        if (!empty($coordinates) && $coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }
        return $coordinates;
    }
}