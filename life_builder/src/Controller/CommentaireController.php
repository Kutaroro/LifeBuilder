<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Personnage;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    // #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    // public function index(CommentaireRepository $commentaireRepository): Response
    // {
    //     return $this->render('commentaire/index.html.twig', [
    //         'commentaires' => $commentaireRepository->findAll(),
    //     ]);
    // }

    // #[Route('/new/{id}', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, PersonnageRepository $personnageRepository, EntityManagerInterface $entityManager,int $id): Response
    // {
    //     $commentaire = new Commentaire();
    //     $form = $this->createForm(CommentaireType::class, $commentaire);
    //     $form->handleRequest($request);

    //     $personnage=$personnageRepository->findOneBy(['id' => $id]);
    //     $personnagesPublics = $entityManager->getRepository(Personnage::class)
    //         ->createQueryBuilder('p')
    //         ->andWhere('p.isPublic = :public')
    //         ->andWhere('p.id != :id')
    //         ->setParameter('public', true)
    //         ->setParameter('id', $personnage->getId())
    //         ->orderBy('p.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $commentaire->setPersonnage($personnage);
    //         $commentaire->setUtilisateur($this->getUser());
    //         $commentaire->setDate(new \DateTimeImmutable());
    //         $entityManager->persist($commentaire);
    //         $entityManager->flush();
    //         var_dump("Commentaire crée");

    //         return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     // Trie des histoires par ordre d'affichage (Valeur nulle à la fin)
    //     $histoires = $personnage->getHistoires()->toArray();
    //     usort($histoires, function($a, $b) { //Trie un tableau en utilisant une fonction de comparaison
    //         $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
    //         $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
    //         return $av <=> $bv;
    //     });

    //     $apparences = $personnage->getApparences()->toArray();
    //     usort($apparences, function($a, $b) { 
    //         $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
    //         $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
    //         return $av <=> $bv;
    //     });

    //     $commentaires= $personnage->getCommentaires()->toArray();

    //     return $this->render('personnage/show.html.twig', [
    //         'commentaire' => $commentaire,
    //         'personnage'=>$personnage,
    //         'personnagesPublics'=>$personnagesPublics,
    //         'histoires'=>$histoires,
    //         'apparences'=>$apparences,
    //         'commentaires'=>$commentaires,
    //         'form'=>$form,
    //     ]);
    // }

    // #[Route('/{id}/repondre', name: 'app_commentaire_repondre', methods: ['GET', 'POST'])]
    // public function repondre(Request $request,int $id, EntityManagerInterface $entityManager, CommentaireRepository $commentaireRepository): Response
    // {
    //     $commentaire=$commentaireRepository->findOneBy(['id' => $id]);
    //     $personnage=$commentaire->getPersonnage();
    //     $reponse = new Commentaire();
    //     $form = $this->createForm(CommentaireType::class, $reponse);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $reponse->setPersonnage($personnage);
    //         $reponse->setCommentaire($commentaire);
    //         $reponse->setMentionedUtilisateur($commentaire->getUtilisateur());
    //         $reponse->setDate(new \DateTimeImmutable());
    //         $entityManager->persist($reponse);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_personnage_show', ['id' => $commentaire->getPersonnage()->getId()], Response::HTTP_SEE_OTHER);
    //     }

    //     // Trie des histoires par ordre d'affichage (Valeur nulle à la fin)
    //     $histoires = $personnage->getHistoires()->toArray();
    //     usort($histoires, function($a, $b) { //Trie un tableau en utilisant une fonction de comparaison
    //         $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
    //         $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
    //         return $av <=> $bv;
    //     });

    //     $apparences = $personnage->getApparences()->toArray();
    //     usort($apparences, function($a, $b) { 
    //         $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
    //         $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
    //         return $av <=> $bv;
    //     });

    //     $commentaires= $personnage->getCommentaires()->toArray();


    //     $personnagesPublics = $entityManager->getRepository(Personnage::class)
    //         ->createQueryBuilder('p')
    //         ->andWhere('p.isPublic = :public')
    //         ->andWhere('p.id != :id')
    //         ->setParameter('public', true)
    //         ->setParameter('id', $personnage->getId())
    //         ->orderBy('p.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();

    //     return $this->render('personnage/show.html.twig', [
    //         'personnage'=>$personnage,
    //         'personnagesPublics'=>$personnagesPublics,
    //         'histoires'=>$histoires,
    //         'apparences'=>$apparences,
    //         'commentaires'=>$commentaires,
    //         'form'=>$form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->getPayload()->getString('_token'))) {
            foreach ($commentaire->getReponses() as $reponse) {
                $entityManager->remove($reponse);
            }
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
    }
}
