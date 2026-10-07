<?php

namespace App\Repository;

use App\Entity\StoryTelling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StoryTelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoryTelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoryTelling[]    findAll()
 * @method StoryTelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoryTellingRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, StoryTelling::class);
	}

	public function getStoryTelllingByEntityId($entityId)
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.entityId = :entityId')
			->setParameter('entityId', $entityId)
			->getQuery()
			->getResult();
	}

	// Add custom query methods here if needed
}
