<?php

namespace App\Repository;

use App\Entity\TreeSpecies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TreeSpeciesRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, TreeSpecies::class);
	}

	public function getAllTreeSpecies() {
		return $this->createQueryBuilder('t')
			->orderBy('t.id', 'DESC')
			->getQuery()
			->getResult();
	}

	// Add custom repository methods if needed
}
