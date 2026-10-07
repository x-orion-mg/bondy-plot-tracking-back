<?php

namespace App\Service;

use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\StoryTelling;
use App\Entity\TreeSpecies;
use App\Entity\User;
use App\Repository\ParcelRepository;
use App\Repository\TreeSpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;

class TreeSpeciesService {
	private ParcelRepository $parcelRepository;
	private TreeSpeciesRepository $treeSpeciesRepository;

	private EntityManagerInterface $entityManager;

	public function __construct( ParcelRepository $parcelRepository, TreeSpeciesRepository $treeSpeciesRepository, EntityManagerInterface $entityManager ) {
		$this->parcelRepository      = $parcelRepository;
		$this->treeSpeciesRepository = $treeSpeciesRepository;
		$this->entityManager         = $entityManager;
	}

	public function aggregateTreeSpecies( array $parcels ): array {
		$treeSpecies = [];
		foreach ( $parcels as $parcel ) {
			$treeCounts = $this->parcelRepository->countTreesByTreeSpecies( $parcel );
			foreach ( $treeCounts as $treeCount ) {
				$treeSpeciesId = $treeCount['treeSpeciesId'];
				if ( ! isset( $treeSpecies[ $treeSpeciesId ] ) ) {
					$treeSpecies[ $treeSpeciesId ]            = $this->treeSpeciesRepository->find( $treeSpeciesId );
					$treeSpecies[ $treeSpeciesId ]->treeCount = 0;
				}
				$treeSpecies[ $treeSpeciesId ]->treeCount += $treeCount['treeCount'];
			}
		}

		return $treeSpecies;
	}

	public function getTreeSpeciesData( array $treeSpecies ): array {
		$treeSpeciesData = [];
		foreach ( $treeSpecies as $species ) {
			$storyTellingSpecies = $this->entityManager->getRepository( StoryTelling::class )->findOneBy( [
				'entityType' => TreeSpecies::class,
				'entityID'   => $species->getId()
			] );
			$treeSpeciesData[]   = [
				'id'             => $species->getId(),
				'commonName'     => $species->getCommonName(),
				'scientificName' => $species->getScientificName(),
				'trackCode'      => $species->getTrackCode(),
				'banner'         => $species->getBanner(),
				'icon'           => $species->getIcon(),
				'usageType'      => $species->getUsageType(),
				'treeCount'      => $species->treeCount,
				'storyTelling'   => $storyTellingSpecies,
				'color'          => $species->getColor(),
			];
		}

		return $treeSpeciesData;
	}

	public function getTreeSpeciesDashboardData( array $treeSpecies ): array {
		$treeSpeciesData = [];
		foreach ( $treeSpecies as $species ) {
			$treeSpeciesData[] = [
				'id'             => $species->getId(),
				'commonName'     => $species->getCommonName(),
				'scientificName' => $species->getScientificName(),
				'treeCount'      => $species->treeCount,
				'color'          => $species->getColor(),
			];
		}

		return $treeSpeciesData;
	}

	public function calculateTotalTreeCountForUser( User $user ): int {
		$projectChildren = $user->getProjectChildren();
		$totalTreeCount  = 0;

		foreach ( $projectChildren as $projectChild ) {
			$parcels = $this->entityManager->getRepository( Parcel::class )->findBy( [ 'projectChild' => $projectChild ] );

			foreach ( $parcels as $parcel ) {
				$parcelTreeSpecies = $this->entityManager->getRepository( ParcelTreeSpecies::class )->findBy( [ 'parcel' => $parcel ] );

				foreach ( $parcelTreeSpecies as $species ) {
					$totalTreeCount += $species->getTreeNumber();
				}
			}
		}

		return $totalTreeCount;
	}
}
