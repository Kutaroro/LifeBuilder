<?php

namespace App\Repository;

use App\Entity\Signalement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Signalement>
 */
class SignalementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Signalement::class);
    }
 
    public function findByType($value,$value2): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.type = :val')
            ->andWhere('s.status != :val2')
            ->setParameter('val', $value)
            ->setParameter('val2', $value2)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByOwner($value): array
    {
        return $this->createQueryBuilder('s')
           
            ->leftJoin('s.mod', 'm') 
            ->andWhere('m.nom = :val')   
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
        
    public function findByKeyword($value): array
       {
           return $this->createQueryBuilder('s')
               ->leftJoin('s.utilisateur', 'u')
               ->leftJoin('s.reportedBy', 'r')
               ->leftJoin('s.personnage', 'p')
               ->leftJoin('s.mod', 'm')
               ->andWhere('LOWER(s.titre) LIKE LOWER(:val)
                                  OR LOWER(s.description) LIKE LOWER(:val)
                                  OR LOWER(s.type) LIKE LOWER(:val)
                                  OR LOWER(s.status) LIKE LOWER(:val)
                                  OR LOWER(u.nom) LIKE LOWER(:val)
                                  OR LOWER(r.nom) LIKE LOWER(:val)
                                  OR LOWER(p.nom) LIKE LOWER(:val)
                                  OR LOWER(m.nom) LIKE LOWER(:val)
                                 ')
               ->setParameter('val', '%'.$value.'%')
               ->orderBy('s.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

    //    /**
    //     * @return Signalement[] Returns an array of Signalement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Signalement
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
