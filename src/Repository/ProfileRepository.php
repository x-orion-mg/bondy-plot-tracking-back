<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProfileRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Profile::class);
	}

	public function getProfileByCode(?string $code) {
        return $this->createQueryBuilder('p')
            ->where('p.code LIKE :code')
            ->setParameter('code', '%' . $code . '%')
            ->getQuery()
            ->getOneOrNullResult();
	}
}

