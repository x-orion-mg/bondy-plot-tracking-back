<?php

namespace App\Repository;

use App\Entity\SIEMEntry;
use App\Entity\Tree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SIEMEntryRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, SIEMEntry::class);
	}

	// Add custom repository methods if needed
}
