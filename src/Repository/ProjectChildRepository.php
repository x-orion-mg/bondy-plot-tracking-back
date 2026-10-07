<?php

namespace App\Repository;

use App\Entity\ProjectChild;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Utils;

class ProjectChildRepository extends ServiceEntityRepository
{
    private $utils;

    public function __construct(
        ManagerRegistry $registry,
        Utils           $utils)
    {
        parent::__construct($registry, ProjectChild::class);
        $this->utils = $utils;
    }

    /**
     * Liste les ProjectChild avec des filtres sur le partner.id et projectStep.id
     *
     * @param string|null $search
     * @param int|null $start
     * @param int|null $length
     * @param int|null $partnerId
     * @param int|null $projectStepId
     * @return ProjectChild[]
     */
    public function getAllProjectChildren(?string $search, ?int $start, ?int $length, ?int $partnerId = 0, ?int $projectStepId = 0): array
    {
        $results = $data = array();
        $qb = $this->createQueryBuilder('pc')
            ->addSelect('pc.id')
            ->addSelect('pc.city')
            ->addSelect('partner.logo AS partner_logo')
            ->addSelect('prj_type.label AS project_name')
            ->addSelect('prj_type.color AS color')
            ->addSelect('localisation.country AS country')
            ->addSelect('localisation.region AS region')
            ->addSelect('pc.startDate')
            ->addSelect('step.projectStepName AS step_name')
            ->leftJoin('pc.projectParent', 'prj_parent')  // Assurer que ProjectParent est non nul
            ->leftJoin('pc.projectStep', 'step')    // Assurer que ProjectStep est non nul
            ->leftJoin('pc.user', 'partner')        // Assurer que Partner est non nul
            ->leftJoin('prj_parent.localisation', 'localisation')
            ->leftJoin('prj_parent.projectType', 'prj_type')
            ->where('pc.projectParent IS NOT NULL')
            ->andWhere('pc.projectStep IS NOT NULL')
            ->andWhere('pc.user IS NOT NULL')
            ->andWhere('prj_parent.localisation IS NOT NULL')
            ->andWhere('prj_parent.projectType IS NOT NULL')
            ->andWhere('pc.startDate IS NOT NULL')
            ->addOrderBy('pc.id', 'DESC');

        if ($partnerId > 0) {
            $qb->andWhere('partner.id = :partnerId')
                ->setParameter('partnerId', $partnerId);
        }
        if ($projectStepId > 0) {
            $qb->andWhere('step.id = :projectStepId')
                ->setParameter('projectStepId', $projectStepId);
        }
        if (!empty($search)) {
            $qb->andWhere('prj_type.label LIKE :search OR localisation.country LIKE :search OR localisation.region LIKE :search OR pc.city LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $qb->setFirstResult($start)
            ->setMaxResults($length);

        $results = $qb->getQuery()->getResult();
        if ($results) {
            foreach ($results as $value) {
                $dateStart = !empty($value['startDate']) ? $this->utils->formatDate($value['startDate']) : null;
                $data[] = [
                    'id' => $value['id'],
                    'logo' => $value['partner_logo'],
                    'name' => $value['project_name'],
                    'color' => !empty($value['color']) ? $value['color'] : '#1B5A2E',
                    'location' => $value['country'] . ' - ' . $value['region'] . ' - ' . $value['city'],
                    'startDate' => $dateStart,
                    'stepName' => $value['step_name'],
                ];
            }
        }

        return $data;
    }

    // Repository Method: Get Total Count of Project Children
    public function countAllProjectChildren(int $partnerId, int $stepId)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'partner')
            ->leftJoin('p.projectStep', 'step')
            ->where('p.user = :partnerId OR :partnerId = 0')
            ->andWhere('p.projectStep = :stepId OR :stepId = 0')
            ->setParameter('partnerId', $partnerId)
            ->setParameter('stepId', $stepId);

        return (int)$qb->select('COUNT(p)')->getQuery()->getSingleScalarResult();
    }

    public function countAllFilteredProjectChildren(string $search, int $partnerId, int $stepId): int
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.projectParent', 'prj_parent')  // Assurer que ProjectParent est non nul
            ->leftJoin('p.projectStep', 'step')    // Assurer que ProjectStep est non nul
            ->leftJoin('p.user', 'partner')        // Assurer que Partner est non nul
            ->leftJoin('prj_parent.localisation', 'localisation')
            ->leftJoin('prj_parent.projectType', 'prj_type');
        if (!empty($search)) {
            $qb->andWhere('prj_type.label LIKE :search OR localisation.country LIKE :search OR localisation.region LIKE :search OR p.city LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($partnerId > 0) {
            $qb->andWhere('partner.id = :partnerId')
                ->setParameter('partnerId', $partnerId);
        }
        if ($stepId > 0) {
            $qb->andWhere('step.id = :stepId')
                ->setParameter('stepId', $stepId);
        }
        return (int)$qb->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();
    }

    public function findProjectChildById(int $id): ?ProjectChild
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countProjectByPartner(int $partnerId): int
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.user = :partnerId')
            ->setParameter('partnerId', $partnerId);

        return (int)$qb->select('COUNT(p)')->getQuery()->getSingleScalarResult();
    }

    public function findProjectParentByPartner(?string $uuid, int $typeId = 0): array
    {
        if (empty($uuid)) {
            return [];
        }

        $qb = $this->createQueryBuilder('child')
            ->select('child', 'parent', 'location', 'status', 'type')
            ->leftJoin('child.user', 'partner')
            ->leftJoin('child.projectParent', 'parent')
            ->leftJoin('parent.localisation', 'location')
            ->leftJoin('parent.projectStatus', 'status')
            ->leftJoin('parent.projectType', 'type')
            ->where('parent.localisation IS NOT NULL')
            ->andWhere('parent.projectStatus IS NOT NULL')
            ->andWhere('parent.projectType IS NOT NULL')
            ->addOrderBy('parent.id', 'DESC');

        if ($typeId > 0) {
            $qb->andWhere('type.id = :typeId')
                ->setParameter('typeId', $typeId);
        }

        $queryResults = $qb->getQuery()->getResult();
        $results = [];
        if (!empty($queryResults)) {
            foreach ($queryResults as $value) {
                $parent = $value->getProjectParent();
                $location = $parent->getLocalisation();
                $status = $parent->getProjectStatus();
                $type = $parent->getProjectType();
                $startDate = $value->getStartDate() ? $this->utils->formatDate($value->getStartDate()) : null;
                $partner = $value->getUser();

                if ($uuid == $partner->getUuid()) {
                    $results[] = [
                        'id' => $parent->getId(),
                        'title' => $parent->getTitle() ?: $type->getLabel(),
                        'city' => $parent->getCity(),
                        'startDate' => $startDate,
                        'history' => $parent->getHistory(),
                        'bannerImage' => $parent->getBannerImage(),
                        'localisation' => (object)[
                            'id' => $location->getId(),
                            'country' => $location->getCountry(),
                            'region' => $location->getRegion(),
                            'shapeId' => $location->getShapeId(),
                            'latitude' => $location->getLatitude(),
                            'longitude' => $location->getLongitude(),
                            'bounds' => $location->getBounds(),
                        ],
                        'projectType' => (object)[
                            'id' => $type->getId(),
                            'label' => $type->getLabel(),
                            'color' => $type->getColor(),
                        ],
                        'projectStatus' => (object)[
                            'id' => $status->getId(),
                            'label' => $status->getLabel(),
                            'slug' => $status->getSlug(),
                        ]
                    ];
                }
            }
        }

        return $results;
    }
}
