<?php

namespace App\Service;

use App\Entity\AllocatedTree;
use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\User;
use App\Repository\ParcelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class TreeService {
	private ParcelRepository $parcelRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(ParcelRepository $parcelRepository, EntityManagerInterface $entityManager) {
		$this->parcelRepository = $parcelRepository;
		$this->entityManager = $entityManager;
	}

	public function getTreeData(int $parentId, int $partnerId, int $childId, int $parcelId, string $trackCode): ?array {
		$parcel = $this->parcelRepository->findParcelWithProjectChild($parcelId, $parentId, $partnerId, $childId);

		if (!$parcel) {
			return ['error' => 'Parcel not found', 'status' => Response::HTTP_NOT_FOUND];
		}

		$allocatedTree = $this->entityManager->getRepository(AllocatedTree::class)->findOneBy([
			'parcel' => $parcel,
			'trackCode' => $trackCode
		]);

		$matchedTree = $this->findMatchedTree($parcel->getTreesJson(), $trackCode);

		if (!$matchedTree) {
			return ['error' => 'Tree not found', 'status' => Response::HTTP_NOT_FOUND];
		}

		$projectChild = $parcel->getProjectChild();
		$partner = $projectChild?->getUser();

		$projectParent = $projectChild->getProjectParent();
		$region = $projectParent->getRegion();
		$city = $projectParent->getTown();
		$country = $region->getCountry();

		$localisation = [
			'country' => $country->getLabel(),
			'region' => $region->getLabel(),
			'city' => $city->getLabel()
		];

		return [
			'parcel' => $parcel,
			'tree' => $matchedTree,
			'allocatedTree' => $allocatedTree,
			'partner' => $partner,
			'localisation' => $localisation
		];
	}

	private function findMatchedTree(array $treesJson, string $trackCode): ?array {
		foreach ($treesJson as $tree) {
			if (isset($tree['id']) && $tree['id'] === $trackCode) {
				return $tree;
			}
		}

		return null;
	}

	public function getTreeCountForProjectParent(ProjectParent $projectParent): int {
		$treeCount = 0;
		$projectChildren = $this->entityManager->getRepository(ProjectChild::class)->findBy(['projectParent' => $projectParent]);

		if (empty($projectChildren)) {
			return $treeCount;
		}

		foreach ($projectChildren as $projectChild) {
			$parcels = $this->entityManager->getRepository(Parcel::class)->findBy(['projectChild' => $projectChild]);
			if (empty($parcels)) {
				continue;
			}

			foreach ($parcels as $parcel) {
				$parcelTreeSpecies = $this->entityManager->getRepository(ParcelTreeSpecies::class)->findBy(['parcel' => $parcel]);
				if (empty($parcelTreeSpecies)) {
					continue;
				}
				foreach ($parcelTreeSpecies as $parcelTreeSpecie) {
					$treeCount += $parcelTreeSpecie->getTreeNumber();
				}
			}
		}

		return $treeCount;
	}
}
