<?php
// src/Repository/AllocatedTreeRepository.php

namespace App\Repository;

use App\Entity\AllocatedTree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AllocatedTreeRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AllocatedTree::class);
	}

	public function findUsersByParcel(int $parcelId): array
	{
		$qb = $this->createQueryBuilder('at')
		           ->select('u')
		           ->join('at.user', 'u')
		           ->where('at.parcel = :parcelId')
		           ->setParameter('parcelId', $parcelId);

		return $qb->getQuery()->getResult();
	}

	public function findByParcelAndTreeSpecies(int $parcelId, array $treeSpeciesIds): array
	{
		return $this->createQueryBuilder('at')
		            ->where('at.parcel = :parcelId')
		            ->andWhere('at.treeSpecies IN (:treeSpeciesIds)')
		            ->setParameter('parcelId', $parcelId)
		            ->setParameter('treeSpeciesIds', $treeSpeciesIds)
		            ->getQuery()
		            ->getResult();
	}

    public function findAllocatedTreeByUserId(int $userId, array $parcelIds): array
    {
        return $this->createQueryBuilder('at')
            ->where('at.user = :userId')
            ->andWhere('at.parcel IN (:parcelIds)')
            ->setParameter('userId', $userId)
            ->setParameter('parcelIds', $parcelIds)
            ->getQuery()
            ->getResult();
    }
}
