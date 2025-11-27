<?php

namespace App\Controller;

use App\Entity\Commentaire;
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

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $personnage=$personnageRepository->findOneBy(['id' => $id]);
    //         $commentaire->setPersonnage($personnage);
    //         $commentaire->setUtilisateur($this->getUser());
    //         $commentaire->setDate(new \DateTimeImmutable());
    //         $entityManager->persist($commentaire);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('personnage/show.html.twig', [
    //         'commentaire' => $commentaire,
    //         'formC' => $form,
    //     ]);
    // }

    #[Route('/{id}/repondre', name: 'app_commentaire_repondre', methods: ['GET', 'POST'])]
    public function repondre(Request $request, Commentaire $parentCommentaire, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setPersonnage($parentCommentaire->getPersonnage());
            $reponse->setCommentaire($parentCommentaire);
            $reponse->setDate(new \DateTimeImmutable());
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_personnage_show', ['id' => $parentCommentaire->getPersonnage()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('personnage/show.html.twig', [
            'commentaire' => $reponse,
            'formR' => $form,
        ]);
    }

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
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
