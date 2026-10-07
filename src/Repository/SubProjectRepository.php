<?php

namespace App\Repository;

use App\Entity\SubProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubProject>
 */
class SubProjectRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, SubProject::class);
	}

	/**
	 * Récupérer tous les sous-projets associés à un projet enfant spécifique
	 */
	public function findByProjectChildId(int $projectChildId): array
	{
		return $this->createQueryBuilder('sp')
		            ->andWhere('sp.project = :projectChildId')
		            ->setParameter('projectChildId', $projectChildId)
		            ->getQuery()
		            ->getResult();
	}

	/**
	 * Récupérer les sous-projets par leur nom
	 */
	public function findByName(string $name): array
	{
		return $this->createQueryBuilder('sp')
		            ->andWhere('sp.name LIKE :name')
		            ->setParameter('name', '%' . $name . '%')
		            ->getQuery()
		            ->getResult();
	}

	/**
	 * Récupérer un sous-projet spécifique par son ID
	 */
	public function findOneById(int $id): ?SubProject
	{
		return $this->createQueryBuilder('sp')
		            ->andWhere('sp.id = :id')
		            ->setParameter('id', $id)
		            ->getQuery()
		            ->getOneOrNullResult();
	}
}
