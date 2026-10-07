<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 */
class GalleryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Gallery::class);
	}

	/**
	 * Ajouter une nouvelle galerie.
	 */
	public function add(Gallery $gallery, bool $flush = false): void
	{
		$this->_em->persist($gallery);

		if ($flush) {
			$this->_em->flush();
		}
	}

	/**
	 * Supprimer une galerie.
	 */
	public function remove(Gallery $gallery, bool $flush = false): void
	{
		$this->_em->remove($gallery);

		if ($flush) {
			$this->_em->flush();
		}
	}

	/**
	 * Trouver toutes les galeries associées à un ProjetParent.
	 */
	public function findByProjectParent(int $projectParentId): array
	{
		return $this->createQueryBuilder('g')
		            ->where('g.projetParent = :projetParentId')
		            ->setParameter('projetParentId', $projectParentId)
		            ->getQuery()
		            ->getResult();
	}

	/**
	 * Trouver toutes les galeries associées à un ProjetChild.
	 */
	public function findByProjectChild(int $projectChildId): array
	{
		return $this->createQueryBuilder('g')
		            ->where('g.projetChild = :projetChildId')
		            ->setParameter('projetChildId', $projectChildId)
		            ->getQuery()
		            ->getResult();
	}

	/**
	 * Rechercher des galeries par type (exemple : 'image', 'video').
	 */
	public function findByType(string $type): array
	{
		return $this->createQueryBuilder('g')
		            ->where('g.type = :type')
		            ->setParameter('type', $type)
		            ->getQuery()
		            ->getResult();
	}
}
