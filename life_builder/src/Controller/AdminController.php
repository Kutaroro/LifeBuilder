<?php

namespace App\Controller;

use App\Entity\Moderateur;
use App\Entity\Signalement;
use App\Entity\Utilisateur;
use App\Repository\SignalementRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\ByteString;

final class AdminController extends AbstractController
{
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    #[Route('/admin', name: 'app_admin')]
    public function index(SignalementRepository $signalementRepository): Response
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

        return $this->render('admin/index.html.twig', [
            'reports' => $reports,
        ]);
    }


    #[IsGranted(new Expression(
    'is_granted("ROLE_ADMIN") or (is_granted("ROLE_MODERATOR") and user.getCategory() == "Utilisateur")'))]
    #[Route('/admin/utilisateurs', name: 'app_admin_utilisateurs')]
    public function utilisateurs(UtilisateurRepository $utilisateurRepository): Response
    {

        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $users = $utilisateurRepository->findAllForAdmin();
        } 
        elseif ($user instanceof Moderateur) {
            $users = $utilisateurRepository->findAllForMods();
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


    #[Route('/admin/create', name: 'app_admin_create')]
    public function createAdmin(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $admin = new Utilisateur();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']); // C'est ici que la magie opère
        // On encode le mot de passe via UserPasswordHasherInterface
        $hashedPassword = $passwordHasher->hashPassword($admin, 'admin@test.com');
        $admin->setPassword($hashedPassword);
        $admin->setNom(ByteString::fromRandom(8)->toString());
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setModifiedAt(new \DateTimeImmutable());   

        $entityManager->persist($admin);
        $entityManager->flush();


        $modoP = new Moderateur();
        $modoP->setEmail('modoP@test.com');
        $modoP->setRoles(['ROLE_MODERATOR']); 
        $modoP->setCategory('Personnage');   
        $modoP->setPassword($passwordHasher->hashPassword($modoP, 'modoP@test.com'));
        $modoP->setNom(ByteString::fromRandom(8)->toString());
        $modoP->setCreatedAt(new \DateTimeImmutable());
        $modoP->setModifiedAt(new \DateTimeImmutable());   
        $entityManager->persist($modoP);
        $entityManager->flush();

        $modoU = new Moderateur();
        $modoU->setEmail('modoU@test.com');
        $modoU->setRoles(['ROLE_MODERATOR']); // On lui donne son rôle
        $modoU->setCategory('Utilisateur');     // On remplit le champ spécifique à l'enfant
        $modoU->setPassword($passwordHasher->hashPassword($modoU, 'modoU@test.com'));
        $modoU->setNom(ByteString::fromRandom(8)->toString());
        $modoU->setCreatedAt(new \DateTimeImmutable());
        $modoU->setModifiedAt(new \DateTimeImmutable());   
        $entityManager->persist($modoU);
        $entityManager->flush();
         
        return new Response('Admins et modérateurs créés avec succès !');
      

    }
}
