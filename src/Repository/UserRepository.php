<?php

namespace App\Repository;

use App\Entity\User;
use App\Utils\EntityName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $email
     * @return User|null
     */
    
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findAllUserPartner(?int $profileId = 0, $order = 'DESC'): array
    {
        $results = [];
        if ($profileId > 0){
            $qb = $this->createQueryBuilder('u')
                ->leftJoin(EntityName::ENTITY_PROJECT_CHILD, 'p', 'WITH', 'p.user = u.id')
                ->where('u.profile = :profileId')
                ->andWhere('u.enabled = 1')
                ->setParameter('profileId', $profileId)
                ->groupBy('u.id')
                ->orderBy('u.id', $order)
                ->select('u.id, u.logo, u.lastName, u.email, u.address, u.phone, u.createdAt, u.enabled, COUNT(p.id) as projectCount');

            $queryResult = $qb->getQuery()->getArrayResult();
            if ($queryResult) {
                foreach ($queryResult as $value) {
                    $isCertificate = $this->getEntityManager()->getRepository(EntityName::ENTITY_CERTIFICATE)->findOneBy(['user' => $value['id']]);
                    $value['hasCertificate'] = (bool) $isCertificate;
                    $results[] = $value;
                }
            }
        }

        return $results;
    }

    public function findPartnerProject(?int $profileId = 0, ?int $partnerId = 0, string $order = 'DESC'): int {
        // Build the query
        $qb = $this->createQueryBuilder('u')
            ->select('p')
            ->leftJoin(EntityName::ENTITY_PROJECT_CHILD, 'p', 'WITH', 'p.user = u.id')
            ->where('u.profile = :profileId')
            ->andWhere('p.user = :partnerId')
            ->andWhere('u.enabled = 1')
            ->andWhere('u.id = :partnerId')
            ->setParameter('profileId', $profileId)
            ->setParameter('partnerId', $partnerId)
            ->groupBy('p.id')
            ->orderBy('p.id', $order);

        return count($qb->getQuery()->getResult());
    }

    public function findUsersByAdminProfiles(?bool $isEnable = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.profile', 'p')
            ->where('p.code IN (:codes)') // Filtrer par les codes ADMIN et SUPER_ADMIN
            ->setParameter('codes', ['ADMIN', 'SUPER_ADMIN'])
            ->orderBy('u.id', 'DESC');

        if ($isEnable !== null) {
            $qb->andWhere('u.enabled = :isEnable')
                ->setParameter('isEnable', $isEnable);
        }

        return $qb->getQuery()->getResult();
    }
}
