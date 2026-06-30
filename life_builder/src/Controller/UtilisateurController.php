<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Personnage;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profil')]
final class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur/{id}', name: 'app_utilisateur')]
    public function index(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    
#[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit( Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher ): Response {
        
        $currentUser = $this->getUser();

       // Si on veut modifier un profil qui n'est pas le sien et qu'on est pas admin ou mod user, accès refusé
        if ($currentUser !== $utilisateur && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce profil.");
        }    
        
    
    
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file=$form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/users',
                    $newFilename
                );

                $utilisateur->setImage($newFilename);
            }


                     
            $newPassword = $form->get('password')->getData();

            if (!empty($newPassword)) {
                //Si on mets un mot de passe, on le hash et on le met à jour
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
                $utilisateur->setPassword($hashedPassword);
            }
            $utilisateur->setModifiedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MODERATOR')) {
                return $this->redirectToRoute('app_admin_profil', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('app_utilisateur', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }
        

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request,Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur->getCommentaires());
            $personnages = $entityManager->getRepository(Personnage::class)->findBy(['utilisateur' => $utilisateur]);
            foreach ($personnages as $personnage) {
                $entityManager->remove($personnage->getApparences());
                $entityManager->remove($personnage->getHistoires());
                $entityManager->remove($personnage);
            }
            $entityManager->remove($utilisateur->getCommentaires());

            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_personnage_catalogue', [], Response::HTTP_SEE_OTHER);
    }
}
