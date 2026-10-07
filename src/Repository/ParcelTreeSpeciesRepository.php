<?php

namespace App\Repository;

use App\Entity\ParcelTreeSpecies;
use App\Repository\TreeSpeciesRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parcel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parcel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parcel[]    findAll()
 * @method Parcel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelTreeSpeciesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                $registry,
        private EntityManagerInterface $entityManager,
        private TreeSpeciesRepository  $treeSpeciesRepository)
    {
        parent::__construct($registry, ParcelTreeSpecies::class);
        $this->entityManager = $entityManager;
        $this->treeSpeciesRepository = $treeSpeciesRepository;
    }

    public function getTreeSpeciesByParcel(?int $parcelId)
    {
        return $this->createQueryBuilder('prc_tree')
            ->leftJoin('prc_tree.parcel', 'parcel')
            ->andWhere('parcel.id = :parcelId')
            ->setParameter('parcelId', $parcelId)
            ->getQuery()
            ->getResult();
    }

    public function getParcelTreeSpeciesInfos(?int $parcelId)
    {
        $results = $this->createQueryBuilder('prc_tree')
            ->select(
                'parcel.id AS parcelId',
                'parcel.title AS parcelTitle',
                'treeSpecies.commonName AS treeSpeciesName',
                'prc_tree.treeNumber',
                'prc_tree.availableTreeNumber'
            )
            ->leftJoin('prc_tree.parcel', 'parcel')
            ->leftJoin('prc_tree.treeSpecies', 'treeSpecies')
            ->andWhere('parcel.id = :parcelId')
            ->setParameter('parcelId', $parcelId)
            ->getQuery()
            ->getArrayResult(); // Utilisation de getArrayResult() au lieu de getResult()

        $aggregatedResults = [];

        // Assure que la sortie est un simple tableau
        return array_values($aggregatedResults);
    }

    /**
     * @param array $treesData
     * @param $parcel
     *
     * @return void
     */
    public function saveParcelTreesSpecies(array $treesData, $parcel): void
    {
        foreach ($treesData as $tree) {
            $treeSpecies = $this->treeSpeciesRepository->find($tree["id"] ?? 0);
            if (!$treeSpecies) {
                continue; // Skip if the tree species does not exist
            }

            $existParcelTreeSpecies = $this->findOneBy([
                'parcel' => $parcel,
                'treeSpecies' => $treeSpecies
            ]);

            if ($existParcelTreeSpecies) {
                // Update the total and available tree numbers
                $currentTotal = $existParcelTreeSpecies->getTreeNumber();
                $currentAvailable = $existParcelTreeSpecies->getAvailableTreeNumber();

                $newTotal = $tree["number"];
                $newAvailable = $currentAvailable + ($newTotal - $currentTotal);

                $existParcelTreeSpecies->setTreeNumber($newTotal);
                $existParcelTreeSpecies->setAvailableTreeNumber($newAvailable);
            } else {
                // Create a new ParcelTreeSpecies if it does not exist
                $parcelTreeSpecies = new ParcelTreeSpecies();
                $parcelTreeSpecies->setParcel($parcel);
                $parcelTreeSpecies->setTreeSpecies($treeSpecies);
                $parcelTreeSpecies->setTreeNumber($tree["number"]);
                $parcelTreeSpecies->setAvailableTreeNumber($tree["number"]);

                $this->entityManager->persist($parcelTreeSpecies);
            }
        }

        $this->entityManager->flush();
    }

}
