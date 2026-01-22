<?php

namespace App\Repository;

use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personnage>
 */
class PersonnageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnage::class);
    }

    // src/Repository/PersonnageRepository.php

public function findTopPopulaires(int $limit ): array
{
    return $this->createQueryBuilder('p')
        // On joint les commentaires (remplace 'commentaires' par le nom de ta propriété dans l'entité Personnage)
        ->leftJoin('p.commentaires', 'c')
        // On sélectionne le personnage et on compte les commentaires
        ->addSelect('COUNT(c) AS HIDDEN count_comments')
        ->groupBy('p.id')
        // On trie par le nombre de commentaires décroissant
        ->orderBy('count_comments', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}


//    /**
//     * @return Personnage[] Returns an array of Personnage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Personnage
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
