<?php

namespace App\Controller;

use App\Entity\Personnage;
use App\Entity\Signalement;
use App\Entity\Utilisateur;
use App\Form\SignalementType;
use App\Repository\SignalementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/signalement')]
final class SignalementController extends AbstractController
{
    // #[Route(name: 'app_signalement_index', methods: ['GET'])]
    // public function index(SignalementRepository $signalementRepository): Response
    // {
    //     return $this->render('signalement/index.html.twig', [
    //         'signalements' => $signalementRepository->findAll(),
    //     ]);
    // }
    #[IsGranted("ROLE_USER")]
    #[Route('/report/user={id}', name: 'app_signalement_user', methods: ['GET', 'POST'])]
    public function newReportUser(Request $request, EntityManagerInterface $entityManager,Utilisateur $utilisateur): Response
    {

        // On récupère la session
        $session = $request->getSession();

        // SI c'est le premier affichage (GET) et qu'on vient d'ailleurs
        if ($request->isMethod('GET')) {
            $referer = $request->headers->get('referer');
            // On stocke l'URL d'origine en session
            $session->set('url_origine', $referer);
        }

        $signalement = new Signalement();
        
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signalement->setUtilisateur($utilisateur);
            $signalement->setReportedBy($this->getUser());
            $signalement->setDate(new \DateTimeImmutable());
            $signalement->setStatus('En attente');
            $signalement->setType('Utilisateur');
            $entityManager->persist($signalement);
            $entityManager->flush();

            // 1. On récupère l'URL de la page d'avant depuis la requête
            // On récupère l'URL mémorisée
            $urlRetour = $session->get('url_origine');

            if ($urlRetour) {
                // On nettoie la session
                $session->remove('url_origine');
                return $this->redirect($urlRetour);
            }
            return $this->redirectToRoute('app_personnage_catalogue', [], Response::HTTP_SEE_OTHER);
            }

        return $this->render('signalement/new.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/report/character={id}', name: 'app_signalement_personnage', methods: ['GET', 'POST'])]
    public function newReportCharacter(Request $request, EntityManagerInterface $entityManager,Personnage $personnage): Response
    {
        // On récupère la session
        $session = $request->getSession();

        // SI c'est le premier affichage (GET) et qu'on vient d'ailleurs
        if ($request->isMethod('GET')) {
            $referer = $request->headers->get('referer');
            // On stocke l'URL d'origine en session
            $session->set('url_origine', $referer);
        }

        $signalement = new Signalement();
        
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signalement->setPersonnage($personnage);
            $signalement->setUtilisateur($personnage->getUtilisateur());
            $signalement->setReportedBy($this->getUser());
            $signalement->setDate(new \DateTimeImmutable());
            $signalement->setStatus('En attente');
            $signalement->setType('Personnage');
            $entityManager->persist($signalement);
            $entityManager->flush();

            // 1. On récupère l'URL de la page d'avant depuis la requête
            // On récupère l'URL mémorisée
            $urlRetour = $session->get('url_origine');

            if ($urlRetour) {
                // On nettoie la session
                $session->remove('url_origine');
                return $this->redirect($urlRetour);
            }

            return $this->redirectToRoute('app_personnage_catalogue', [], Response::HTTP_SEE_OTHER);
        }
        


        return $this->render('signalement/new.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }

     #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/{id}', name: 'app_signalement_show', methods: ['GET'])]
    public function show(Signalement $signalement): Response
    {
        return $this->render('signalement/show.html.twig', [
            'signalement' => $signalement,
        ]);
    }


    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/take/{id}', name: 'app_signalement_take', methods: ['POST'])]
    public function takeTicket(EntityManagerInterface $entityManager, Signalement $signalement, Request $request): Response
    {   
        if ($this->isCsrfTokenValid('take'.$signalement->getId(), $request->getPayload()->getString('_token'))) {
            $signalement->setMod($this->getUser());
            $signalement->setStatus('En cours de traitement');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/close/{id}', name: 'app_signalement_close', methods: ['POST'])]
    public function closeTicket(EntityManagerInterface $entityManager, Signalement $signalement, Request $request): Response
    {   
        if ($this->isCsrfTokenValid('close'.$signalement->getId(), $request->getPayload()->getString('_token'))) {
            $signalement->setStatus('Traité');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }


    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/{id}/edit', name: 'app_signalement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Signalement $signalement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signalement/edit.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/{id}', name: 'app_signalement_delete', methods: ['POST'])]
    public function delete(Request $request, Signalement $signalement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signalement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($signalement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
    }
}
