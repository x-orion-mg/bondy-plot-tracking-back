<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\SubProject;
use App\Service\TreeService;
use App\Utils\Utils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * @extends ServiceEntityRepository<ProjectParent>
 */
class ProjectParentRepository extends ServiceEntityRepository {
	private $utils;
	private $packages;
	private $requestStack;
	private TreeService $treeService;

	public function __construct( ManagerRegistry $registry, Utils $utils, Packages $packages, RequestStack $requestStack, TreeService $treeService ) {
		parent::__construct( $registry, ProjectParent::class );
		$this->utils        = $utils;
		$this->packages     = $packages;
		$this->requestStack = $requestStack;
		$this->treeService  = $treeService;

	}

	public function fetchProjectParentById( int $id ): ?ProjectParent {
		$qb = $this->createQueryBuilder( 'p' )
		           ->select( 's', 't', 'l', 'p' )
		           ->leftJoin( 'p.localisation', 'l' )
		           ->leftJoin( 'p.projectStatus', 's' )
		           ->leftJoin( 'p.projectType', 't' )
		           ->where( 'p.id = :id' )
		           ->setParameter( 'id', $id )
		           ->setMaxResults( 1 );

		$projectParent = $qb->getQuery()->getOneOrNullResult();

		if ( $projectParent ) {
			$request           = $this->requestStack->getCurrentRequest();
			$galleryRepository = $this->getEntityManager()->getRepository( Gallery::class );
			$galleries         = $galleryRepository->findBy( [
				'entityType' => ProjectParent::class,
				'entityId'   => $id
			] );
			$projectParent->setGalleries( $galleries );

			$subprojectRepository = $this->getEntityManager()->getRepository( SubProject::class );
			$subprojects          = $subprojectRepository->findBy( [
				'entityType' => ProjectParent::class,
				'entityId'   => $id
			] );
			$projectParent->setSubprojects( $subprojects );

			$startDate = $this->utils->formatDate( $projectParent->getStartDate() );
			$projectParent->setStartDateFormatted( $startDate );

		}

		return $projectParent;
	}

	public function getAllProjectParent( ?string $search, ?int $start, ?int $length, ?int $statusId = 0, ?int $typeId = 0, ?bool $isApi = false ): array {
		$results = $data = array();
		$qb      = $this->createQueryBuilder( 'parent' )
		                ->select( 'parent', 'location', 'status', 'type' )
		                ->leftJoin( 'parent.localisation', 'location' )
		                ->leftJoin( 'parent.projectStatus', 'status' )
		                ->leftJoin( 'parent.projectType', 'type' )
		                ->where( 'parent.localisation IS NOT NULL' )
		                ->andWhere( 'parent.projectStatus IS NOT NULL' )
		                ->andWhere( 'parent.projectType IS NOT NULL' )
		                ->orderBy( 'parent.id', 'DESC' );

		if ( $statusId > 0 ) {
			$qb->andWhere( 'status.id = :statusId' )
			   ->setParameter( 'statusId', $statusId );
		}
		if ( $typeId > 0 ) {
			$qb->andWhere( 'type.id = :typeId' )
			   ->setParameter( 'typeId', $typeId );
		}

		if ( ! empty( $search ) ) {
			$qb->andWhere( 'status.label LIKE :search OR type.label LIKE :search OR location.country LIKE :search OR location.region LIKE :search OR parent.city LIKE :search' )
			   ->setParameter( 'search', '%' . $search . '%' );
		}
		$qb->setFirstResult( $start )
		   ->setMaxResults( $length );

		$results = $qb->getQuery()->getResult();
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $value ) {
				$location  = $value->getLocalisation();
				$type      = $value->getProjectType();
				$status    = $value->getProjectStatus();
				$startDate = $value->getStartDate() ? $this->utils->formatDate( $value->getStartDate() ) : null;
				$title     = ! empty( $value->getTitle() ) ? $value->getTitle() : $type->getLabel();
				$treeCount = $this->treeService->getTreeCountForProjectParent( $value );
				if ( $isApi == true ) {
					array_push( $data, [
						'id'            => $value->getId(),
						'title'         => $title,
						'city'          => $value->getCity(),
						'startDate'     => $startDate,
						'history'       => $value->getHistory(),
						'bannerImage'   => $value->getBannerImage(),
						'localisation'  => (object) [
							'id'        => $location->getId(),
							'country'   => $location->getCountry(),
							'region'    => $location->getRegion(),
							'shapeId'   => $location->getShapeId(),
							'latitude'  => $location->getLatitude(),
							'longitude' => $location->getLongitude(),
							'bounds'    => $location->getBounds(),
						],
						'projectType'   => (object) [
							'id'    => $type->getId(),
							'label' => $type->getLabel(),
							'color' => $type->getColor(),
						],
						'projectStatus' => (object) [
							'id'    => $status->getId(),
							'label' => $status->getLabel(),
							'slug'  => $status->getSlug(),
						],
						'treeCount'     => $treeCount
					] );
				} else {
					array_push( $data, [
						'id'        => $value->getId(),
						'title'     => $title,
						'location'  => $location->getCountry() . ' - ' . $location->getRegion() . ' - ' . $value->getCity(),
						'status'    => $status->getLabel(),
						'type'      => $type->getLabel(),
						'color'     => $type->getColor() ?? '#1B5A2E',
						'startDate' => $startDate,
						'treeCount' => $treeCount
					] );
				}
			}

		}

		return $data;
	}

	public function countAllProjectParent( int $statusId, int $typeId ) {
		$qb = $this->createQueryBuilder( 'parent' )
		           ->select( 'COUNT(parent.id)' )
		           ->leftJoin( 'parent.localisation', 'localisation' )
		           ->leftJoin( 'parent.projectStatus', 'status' )
		           ->leftJoin( 'parent.projectType', 'type' )
		           ->where( 'parent.localisation IS NOT NULL' )
		           ->andWhere( 'parent.projectStatus IS NOT NULL' )
		           ->andWhere( 'parent.projectType IS NOT NULL' );

		if ( $statusId > 0 ) {
			$qb->andWhere( 'status.id = :statusId' )
			   ->setParameter( 'statusId', $statusId );
		}
		if ( $typeId > 0 ) {
			$qb->andWhere( 'type.id = :typeId' )
			   ->setParameter( 'typeId', $typeId );
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	public function countAllFilteredProjectParent( string $search, int $statusId, int $typeId ) {
		$qb = $this->createQueryBuilder( 'parent' )
		           ->select( 'COUNT(parent.id)' )
		           ->leftJoin( 'parent.localisation', 'localisation' )
		           ->leftJoin( 'parent.projectStatus', 'status' )
		           ->leftJoin( 'parent.projectType', 'type' )
		           ->where( 'parent.localisation IS NOT NULL' )
		           ->andWhere( 'parent.projectStatus IS NOT NULL' )
		           ->andWhere( 'parent.projectType IS NOT NULL' );

		if ( $statusId > 0 ) {
			$qb->andWhere( 'status.id = :statusId' )
			   ->setParameter( 'statusId', $statusId );
		}
		if ( $typeId > 0 ) {
			$qb->andWhere( 'type.id = :typeId' )
			   ->setParameter( 'typeId', $typeId );
		}

		if ( ! empty( $search ) ) {
			$qb->andWhere( 'status.label LIKE :search OR type.label LIKE :search OR localisation.country LIKE :search OR localisation.region LIKE :search OR parent.city LIKE :search' )
			   ->setParameter( 'search', '%' . $search . '%' );
		}

		return (int) $qb->select( 'COUNT(parent.id)' )->getQuery()->getSingleScalarResult();
	}

	/**
	 * Checks if a project type with the given ID exists in the parent project.
	 *
	 * @param int $id The ID of the project type to check.
	 *
	 * @return mixed|null The project type if it exists, or null otherwise.
	 */
	public function fetchIfExistsInParentProject( int $id ) {
		$result = $this->createQueryBuilder( 'pp' )
		               ->select( 'COUNT(pp.id)' )
		               ->where( 'pp.projectType = :projectTypeId' )
		               ->setParameter( 'projectTypeId', $id )
		               ->getQuery()
		               ->getSingleScalarResult() > 0;

		return $result;
	}

	public function getDataParentLocation( ?int $parentId ) {
		$qb = $this->createQueryBuilder( 'parent' )
		           ->select( 'parent.id' )
		           ->addselect( 'localisation.country' )
		           ->addselect( 'localisation.region' )
		           ->leftJoin( 'parent.localisation', 'localisation' )
		           ->where( 'parent.id = :parentId' )
		           ->setParameter( 'parentId', $parentId );

		return $qb->getQuery()->getOneOrNullResult();
	}

	public function selectProjectBondy() {
		$data = array();
		$qb   = $this->createQueryBuilder( 'parent' )
		             ->select( 'parent.id' )
		             ->addselect( 'localisation.country' )
		             ->addselect( 'localisation.region' )
		             ->addselect( 'type.label' )
		             ->leftJoin( 'parent.localisation', 'localisation' )
		             ->leftJoin( 'parent.projectStatus', 'status' )
		             ->leftJoin( 'parent.projectType', 'type' )
		             ->andWhere( 'parent.localisation IS NOT NULL' )
		             ->andWhere( 'parent.projectStatus IS NOT NULL' )
		             ->andWhere( 'parent.projectType IS NOT NULL' )
		             ->orderBy( 'localisation.id', 'DESC' );

		$results = $qb->getQuery()->getResult();
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $value ) {
				array_push( $data, [
					'id'   => $value['id'],
					'name' => $value['label'] . ' - (' . $value['country'] . ' - ' . $value['region'] . ')'
				] );
			}
		}

		return $data;
	}
}
