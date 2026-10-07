<?php

namespace App\Repository;

use App\Entity\ProjectType;
use App\Utils\Utils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\Curl\Util;

/**
 * @extends ServiceEntityRepository<ProjectType>
 */
class ProjectTypeRepository extends ServiceEntityRepository
{
    private $utils;
    public function __construct(ManagerRegistry $registry, Utils $utils)
    {
        parent::__construct($registry, ProjectType::class);
        $this->utils = $utils;
    }

    
/**
 * Fetches all project types from the database.
 *
 * @return array An array of project types, each represented as an associative array 
 *               containing 'id', 'name', and 'slug' keys.
 */
    public function fetchAllTypes() {
        $data = array();
        $types = $this->createQueryBuilder('p')->getQuery()->getResult();

        if ( !empty($types) ) {
            foreach ($types as $key => $value) {
                array_push($data, [
                    'id' => $value->getId(),
                    'name' => $value->getLabel(),
                    'slug' => $value->getSlug()
                ]);
            }
        }
        return $data;
    }

    /**
     * @param int $id
     * @param string $label
     * @param string $color
     * @return ProjectType
     */
    public function updateType(int $id, string $label, string $color): void
    {
        $project_type = $this->find($id);
        if (!$project_type) {
            throw new \InvalidArgumentException("Project type not found");
        }

        $project_type->setLabel($label);
        $project_type->setSlug($this->utils->slug($label));
        $project_type->setColor($color);

        $this->getEntityManager()->flush();
    }

    /**
     * @param string $label
     * @param string $color
     * @return ProjectType
     */
    public function createType(string $label, string $color): ProjectType
    {
        $project_type = new ProjectType();
        $project_type->setLabel($label);
        $project_type->setSlug($this->utils->slug($label));
        $project_type->setColor($color);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($project_type);
        $entityManager->flush();

        return $project_type;
    }

    /**
     * Deletes a project type from the database.
     *
     * @param int $id The ID of the project type to delete.
     *
     * @throws \InvalidArgumentException If the project type is not found.
     */
    public function deleteType(int $id): void
    {
        $project_type = $this->find($id);
        if (!$project_type) {
            throw new \InvalidArgumentException("Project type with not found");
        }

        $this->getEntityManager()->remove($project_type);
        $this->getEntityManager()->flush();
    }

    /**
     * Finds a project type by its label.
     *
     * @param string $label The label of the project type to find.
     *
     * @return ProjectType|null The project type if it exists, or null otherwise.
     */
    public function findByLabel(string $label) {
        return $this->findOneBy(['label' => $label]);
    }
}
