<?php

namespace App\Repository;

use App\Entity\ErrorType;
use App\Entity\Tree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ErrorTypeRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ErrorType::class);
	}

	// Add custom repository methods if needed
}
