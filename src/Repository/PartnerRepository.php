<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\Utils;

/** * @extends ServiceEntityRepository<Partner> */
class PartnerRepository extends ServiceEntityRepository {

	public function __construct( ManagerRegistry $registry ) {
		parent::__construct( $registry, Partner::class );
	}

    public function getPartnerByEmail( ?string $email = null )
    {
        $qb   = $this->createQueryBuilder( 'p' )
            ->where( 'p.email = :email' )
            ->setParameter( 'email', $email );

        return $qb->getQuery()->getOneOrNullResult();
    }
}
