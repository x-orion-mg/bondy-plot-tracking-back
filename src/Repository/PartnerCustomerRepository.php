<?php

namespace App\Repository;

use App\Entity\PartnerCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Utils;

/**
 * @extends ServiceEntityRepository<PartnerCustomer>
 */
class PartnerCustomerRepository extends ServiceEntityRepository
{
    const DATE_FILTER = 'pc.createdAt >= :dateStart AND pc.createdAt < :dateEnd';
    const PARTNER_FILTER = 'pc.partner = :partnerId';
    const PROFILE_FILTER = 'p.id = :profileId';
    public function __construct(ManagerRegistry $registry, Utils $utils)
    {
        parent::__construct($registry, PartnerCustomer::class);
        $this->utils = $utils;
    }

    public function findByPartnerAndProfile(?int $partnerId, ?int $profileId, ?int $start, ?int $length, $dateStart = '', $dateEnd = '', string $search = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('pc')
            ->select('u.id as userId',
                'u.uuid',
                'u.firstName', 'u.lastName',
                'u.email',
                'p.id as profileId',
                'p.label as profileLabel',
                'pc.createdAt')
            ->innerJoin('pc.user', 'u')
            ->innerJoin('u.profile', 'p')
            ->where(self::PARTNER_FILTER)
            ->andWhere(self::PROFILE_FILTER)
            ->setParameter('partnerId', $partnerId)
            ->setParameter('profileId', $profileId)
            ->orderBy('pc.createdAt', 'DESC');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if (!empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder
                ->andWhere(self::DATE_FILTER)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        // Appliquer la pagination
        if ($start !== null) {
            $queryBuilder->setFirstResult($start);
        }
        if ($length !== null) {
            $queryBuilder->setMaxResults($length);
        }

        $results = $queryBuilder->getQuery()->getResult();
        if ($results) {
            foreach ($results as &$result) {
                if ($result['createdAt'] instanceof \DateTimeInterface) {
                    $result['createdAt'] = $result['createdAt']->format('d/m/Y');
                }
            }
        }

        return $results;
    }

    public function countAllPartnerAndProfile(?int $partnerId, ?int $profileId, $dateStart = '', $dateEnd = ''): int
    {
        $queryBuilder = $this->createQueryBuilder('pc')
            ->select('COUNT(pc)')
            ->innerJoin('pc.user', 'u')
            ->innerJoin('u.profile', 'p')
            ->where(self::PARTNER_FILTER)
            ->andWhere(self::PROFILE_FILTER)
            ->setParameter('partnerId', $partnerId)
            ->setParameter('profileId', $profileId);

        if (!empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder
                ->andWhere(self::DATE_FILTER)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAllFilteredPartnerAndProfile(?int $partnerId, ?int $profileId, $dateStart = '', $dateEnd = '', string $search = ''): int
    {
        $queryBuilder = $this->createQueryBuilder('pc')
            ->select('COUNT(pc)')
            ->innerJoin('pc.user', 'u')
            ->innerJoin('u.profile', 'p')
            ->where(self::PARTNER_FILTER)
            ->andWhere(self::PROFILE_FILTER)
            ->setParameter('partnerId', $partnerId)
            ->setParameter('profileId', $profileId);

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if (!empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder
                ->andWhere(self::DATE_FILTER)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }


}
