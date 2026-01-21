<?php

namespace App\Repository;

use App\Entity\Histoire;
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
