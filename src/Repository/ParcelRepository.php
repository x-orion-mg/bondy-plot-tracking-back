<?php

namespace App\Repository;

use App\Entity\Parcel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parcel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parcel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parcel[]    findAll()
 * @method Parcel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Parcel::class);
	}

    public function findParcelByProject(?int $projectId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select(
                'p.id AS parcelId',
                'p.title AS parcelTitle',
                "GROUP_CONCAT(DISTINCT treeSpecies.commonName ORDER BY treeSpecies.commonName ASC SEPARATOR ', ') AS treeSpeciesNames",
                'SUM(pts.treeNumber) AS totalTreeNumber',
                'SUM(pts.availableTreeNumber) AS totalAvailableTreeNumber',
                'SUM(pts.treeNumber - pts.availableTreeNumber) AS totalAttachedTreeNumber'
            )
            ->leftJoin('p.projectChild', 'project')
            ->leftJoin('p.parcelTreeSpecies', 'pts')
            ->leftJoin('pts.treeSpecies', 'treeSpecies')
            ->andWhere('project.id = :projectId')
            ->setParameter('projectId', $projectId)
            ->groupBy('p.id, p.title')
            ->getQuery();

        return $qb->getArrayResult();
    }

	// Add custom query methods here if needed
	public function countTreesByTreeSpecies(Parcel $parcel): array {
		$qb = $this->createQueryBuilder('p')
           ->select('ts.id AS treeSpeciesId, SUM(pts.treeNumber) AS treeCount')
           ->join('p.parcelTreeSpecies', 'pts')
           ->join('pts.treeSpecies', 'ts')
           ->where('p.id = :parcelId')
           ->setParameter('parcelId', $parcel->getId())
           ->groupBy('ts.id');

		return $qb->getQuery()->getResult();
	}
	public function findParcelWithProjectChild(int $parcelId, int $parentId, int $partnerId, int $childId): ?Parcel {
		return $this->createQueryBuilder('p')
            ->innerJoin('p.projectChild', 'pc')
            ->andWhere('p.id = :parcelId')
            ->andWhere('pc.projectParent = :parentId')
            ->andWhere('pc.user = :partnerId')
            ->andWhere('pc.id = :childId')
            ->setParameter('parcelId', $parcelId)
            ->setParameter('parentId', $parentId)
            ->setParameter('partnerId', $partnerId)
            ->setParameter('childId', $childId)
            ->getQuery()
            ->getOneOrNullResult();
	}

    public function findParcelsIdsByPartnerId(?int $partnerId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id')
            ->innerJoin('p.projectChild', 'pc')
            ->innerJoin('pc.user', 'partner')
            ->where('partner.id = :partnerId')
            ->setParameter('partnerId', $partnerId);

        $results = $qb->getQuery()->getResult();
        return array_map(function($result) {
            return $result['id'];
        }, $results);
    }
}

