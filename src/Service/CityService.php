<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;

class CityService {
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function findOrCreateCity(string $cityLabel, Region $region): City {
		$cityRepository = $this->entityManager->getRepository(City::class);
		$existingCity = $cityRepository->findOneBy(['label' => $cityLabel, 'region' => $region]);

		if ($existingCity) {
			return $existingCity;
		} else {
			$newCity = new City();
			$newCity->setLabel($cityLabel);
			$newCity->setRegion($region);

			$this->entityManager->persist($newCity);
			$this->entityManager->flush();

			return $newCity;
		}
	}
}
