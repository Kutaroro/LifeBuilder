<?php

namespace App\Repository;

use App\Entity\ModStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModStatus>
 */
class ModStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModStatus::class);
    }

    /**
     * Récupère tous les statuts sanctionnés dont la date de fin est dépassée
     * @return ModStatus[]
     */
    public function findExpiredSanctions(\DateTimeInterface $now): array
    {
        return $this->createQueryBuilder('m')
            // On cible uniquement ceux qui ont encore une sanction active textuellement
            ->andWhere('m.status != :noSanction') 
            // ET dont la date de fin est strictement inférieure à "maintenant"
            ->andWhere('m.dateFin < :now')
            // On évite les bugs si jamais la dateFin est nulle
            ->andWhere('m.dateFin IS NOT NULL') 
            
            // On injecte les variables pour éviter les injections SQL
            ->setParameter('noSanction', 'Pas de sanction en cours')
            ->setParameter('now', $now)
            
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return ModStatus[] Returns an array of ModStatus objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ModStatus
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
