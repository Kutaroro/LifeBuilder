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



// public function findByFilters(int $utilisateurId, ?string $search, ?string $category, ?string $tag): array
// {
//     $entityManager = $this->getEntityManager();
//     $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($entityManager);
//     $rsm->addRootEntityFromClassMetadata(Personnage::class, 'p');

//     // Base de la requête
//     $sql = 'SELECT p.* FROM personnage p WHERE p.utilisateur_id = :uId';
//     $params = ['uId' => $utilisateurId];

//     // 1. Recherche globale (input texte)
//     if ($search) {
//         $sql .= ' AND (p.nom ILIKE :search OR p.description ILIKE :search OR p.tags::text ILIKE :search OR p.categories::text ILIKE :search)';
//         $params['search'] = '%' . $search . '%';
//     }

//     // 2. Recherche par catégorie (clic sur badge)
//     if ($category) {
//         $sql .= ' AND p.categories::text ILIKE :cat';
//         $params['cat'] = '%"' . $category . '"%';
//     }

//     // 3. Recherche par tag (clic sur badge)
//     if ($tag) {
//         $sql .= ' AND p.tags::text ILIKE :tag';
//         $params['tag'] = '%"' . $tag . '"%';
//     }

//     return $entityManager->createNativeQuery($sql, $rsm)->setParameters($params)->getResult();
// }






public function findByFilters(int $uId, ?string $search, ?string $cat, ?string $tag): array
{
    $em = $this->getEntityManager();
    $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($em);
    $rsm->addRootEntityFromClassMetadata(Personnage::class, 'p');

    $sql = 'SELECT p.* FROM personnage p WHERE p.utilisateur_id = :uId';
    $params = ['uId' => $uId];

    // 1. Recherche globale
    if ($search) {
        $sql .= ' AND (p.nom ILIKE :search OR p.description ILIKE :search OR p.tags::text ILIKE :search OR p.categories::text ILIKE :search)';
        $params['search'] = "%$search%";
    }

    // 2. Recherche par catégorie (avec conversion JSONB pour l'opérateur ?)
    if ($cat) {
        // Le cast ::jsonb permet d'utiliser l'opérateur ?
        // On double le ? (??) pour que Doctrine ne le confonde pas avec un paramètre
        $sql .= ' AND p.categories::jsonb ?? :cat';
        $params['cat'] = $cat;
    }

    // 3. Recherche par tag
    if ($tag) {
        $sql .= ' AND p.tags::jsonb ?? :tag';
        $params['tag'] = $tag;
    }

    return $em->createNativeQuery($sql, $rsm)->setParameters($params)->getResult();
}




public function findAllPublicByFilters(?string $search, ?string $cat, ?string $tag): array
{
    $em = $this->getEntityManager();
    $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($em);
    $rsm->addRootEntityFromClassMetadata(Personnage::class, 'p');

    // On remplace le filtre utilisateur par is_public = true
    $sql = 'SELECT p.* FROM personnage p WHERE p.is_Public = true';
    $params = [];

    // 1. Recherche globale (nom, description, tags, categories)
    if ($search) {
        $sql .= ' AND (p.nom ILIKE :search OR p.description ILIKE :search OR p.tags::text ILIKE :search OR p.categories::text ILIKE :search)';
        $params['search'] = "%$search%";
    }

    // 2. Recherche par catégorie spécifique
    if ($cat) {
        // Le double point d'interrogation ?? est nécessaire pour PostgreSQL avec Doctrine
        $sql .= ' AND p.categories::jsonb ?? :cat';
        $params['cat'] = $cat;
    }

    // 3. Recherche par tag spécifique
    if ($tag) {
        $sql .= ' AND p.tags::jsonb ?? :tag';
        $params['tag'] = $tag;
    }

    return $em->createNativeQuery($sql, $rsm)->setParameters($params)->getResult();
}
        // public function searchByKeyword(string $keyword, int $utilisateurId): array
        // {
        //     return $this->createQueryBuilder('p')
        //         ->where('p.utilisateur = :utilisateurId')
        //         ->andWhere('(
        //             LOWER(p.nom) LIKE LOWER(:keyword) OR 
        //             LOWER(p.description) LIKE LOWER(:keyword) OR 
        //             LOWER(p.tags) LIKE LOWER(:keyword) OR 
        //             LOWER(p.categories) LIKE LOWER(:keyword)
        //         )')
        //         // Note : On retire le CAST. Si Doctrine râle encore sur le type JSON, 
        //         // on passe par une requête SQL native ou on utilise la Solution 2.
        //         ->setParameter('keyword', '%' . $keyword . '%')
        //         ->setParameter('utilisateurId', $utilisateurId)
        //         ->getQuery()
        //         ->getResult();
        // }

        public function searchByKeyword(string $keyword, int $utilisateurId): array
{
    $entityManager = $this->getEntityManager();
    
    // Le SQL natif permet d'utiliser "::text" pour convertir le JSON
    // et "ILIKE" pour une recherche insensible à la casse (Case Insensitive)
    $sql = '
        SELECT p.* FROM personnage p 
        WHERE p.utilisateur_id = :utilisateurId 
        AND (
            p.nom ILIKE :keyword OR 
            p.description ILIKE :keyword OR 
            p.tags::text ILIKE :keyword OR 
            p.categories::text ILIKE :keyword
        )
    ';

    // On explique à Doctrine comment transformer les lignes SQL en objets Personnage
    $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($entityManager);
    $rsm->addRootEntityFromClassMetadata(Personnage::class, 'p');

    $query = $entityManager->createNativeQuery($sql, $rsm);
    $query->setParameter('keyword', '%' . $keyword . '%');
    $query->setParameter('utilisateurId', $utilisateurId);

    return $query->getResult();
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
