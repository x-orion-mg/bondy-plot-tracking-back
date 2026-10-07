<?php

namespace App\Repository;

use App\Entity\CategoryTreeSpecies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryTreeSpeciesRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, CategoryTreeSpecies::class);
	}

	// Add custom repository methods if needed
}
