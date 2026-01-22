<?php

namespace App\Controller;

use App\Entity\Moderateur;
use App\Entity\ModStatus;
use App\Entity\Signalement;
use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Repository\SignalementRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\Curl\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\ByteString;

final class AdminController extends AbstractController
{
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/admin', name: 'app_admin')]
    public function index(SignalementRepository $signalementRepository, Request $request): Response
    {

        $user = $this->getUser();
        $reports = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $reports = $signalementRepository->findAll();
        } 
        elseif ($user instanceof Moderateur) {
            $categorie = $user->getCategory(); 
            dump($categorie);
            $reports = $signalementRepository->findByType($categorie, 'Traité');
        }

        $query = $request->query->get('search');

        if ($query) {
            $reports = $signalementRepository->findByKeyword($query);
        }

        return $this->render('admin/index.html.twig', [
            'reports' => $reports,
        ]);
    }


    #[IsGranted(new Expression(
    'is_granted("ROLE_ADMIN") or (is_granted("ROLE_MODERATOR") and user.getCategory() == "Utilisateur")'))]
    #[Route('/admin/utilisateurs', name: 'app_admin_utilisateurs')]
    public function utilisateurs(UtilisateurRepository $utilisateurRepository, Request $request): Response
    {

        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $users = $utilisateurRepository->findAllForAdmin();
        } 
        elseif ($user instanceof Moderateur) {
            $users = $utilisateurRepository->findAllForMods();
        }

        $query = $request->query->get('search');

        if ($query) {
            $users = $utilisateurRepository->findByKeyword($query);
        }


        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);

    }

    #[Route('/admin/signlement/{id}', name: 'app_admin_show')]
    public function show(Signalement $signalement): Response
    {   

        return $this->render('admin/show.html.twig', [
            'signalement' => $signalement,
        ]);
    }

    #[Route('/admin/profil/{id}', name: 'app_admin_profil')]
    public function profil(Utilisateur $utilisateur, SignalementRepository $signalementRepository): Response
    {   
        $reports = $signalementRepository->findByOwner($utilisateur->getNom());
        return $this->render('admin/profil.html.twig', [
            'utilisateur' => $utilisateur,
            'reports' => $reports,
        ]);
    }

    #[Route('{id}/Categorie/edit', name: 'app_edit_categorie', methods: ['POST'])]
    public function editCategorie(Request $request, EntityManagerInterface $em, Utilisateur $utilisateur): Response
    {

        $newCategorie = $request->request->get('categorie');
        if (!($utilisateur instanceof Moderateur)) {
            $this->addFlash('error', 'L\'utilisateur n\'est pas un modérateur.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }
        if (in_array($newCategorie, ['Utilisateur', 'Personnage'])) {
            $utilisateur->setCategory($newCategorie);
            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Catégorie mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Catégorie invalide.');
        }   

        return $this->redirectToRoute('app_admin_utilisateurs');
    }


    #[Route('/mod/create', name: 'app_create_mod')]
    public function createAdmin(Request $request, Security $security ,EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $modoP = new Moderateur();
        
        $form = $this->createForm(RegistrationFormType::class, $modoP);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $plainPassword = $form->get('plainPassword')->getData();

            $status= new ModStatus();
            $status->setStatus('Pas de sanction en cours');
            $status->setNbSig(0);
            $modoP->setPassword($userPasswordHasher->hashPassword($modoP, $plainPassword));
            $modoP->setNom(ByteString::fromRandom(8)->toString());           
            $modoP->setCreatedAt(new \DateTimeImmutable());
            $modoP->setModifiedAt(new \DateTimeImmutable());               
            $modoP->setRoles(['ROLE_MODERATOR']); 
            $modoP->setStatus($status);
            $entityManager->persist($modoP);
            $entityManager->flush();

            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        return $this->render('admin/register.html.twig', [
            'registrationForm' => $form,
        ]);

    }
}
