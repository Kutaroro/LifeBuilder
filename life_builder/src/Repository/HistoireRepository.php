<?php

namespace App\Repository;

use App\Entity\Histoire;
use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Histoire>
 */
class HistoireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Histoire::class);
    }

    public function findByKeyword($value, int $personnageId): array
       {
           return $this->createQueryBuilder('h')
               ->where('h.personnage = :pId')
               ->andWhere('LOWER(h.titre) LIKE LOWER(:val)
                                  OR LOWER(h.description) LIKE LOWER(:val)
                                  OR LOWER(h.categorie) LIKE LOWER(:val)')
               ->setParameter('pId', $personnageId)
               ->setParameter('val', '%'.$value.'%')
               ->orderBy('h.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }


    public function findDistinctCategoriesByPersonnage(Personnage $personnage): array
    {
        return $this->createQueryBuilder('h')
            ->select('DISTINCT h.categorie')
            ->where('h.personnage = :p')
            ->andWhere('h.categorie IS NOT NULL')
            ->andWhere("h.categorie != ''")
            ->setParameter('p', $personnage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les histoires d'un personnage ordonnées par position d'affichage
     * * @param Personnage $personnage
     * @return Histoire[]
     */
    public function findByPersonnageOrdered(Personnage $personnage): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.personnage = :p')
            ->setParameter('p', $personnage)
            ->orderBy('h.ordreAffichage', 'ASC')
            ->addOrderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Histoire[] Returns an array of Histoire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Histoire
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
