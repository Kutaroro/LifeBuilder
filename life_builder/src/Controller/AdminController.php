<?php

namespace App\Controller;

use App\Entity\Moderateur;
use App\Entity\Signalement;
use App\Entity\Utilisateur;
use App\Repository\SignalementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\ByteString;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager,SignalementRepository $signalementRepository): Response
    {
    
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $reports = [];
        dump($reports);
        if ($this->isGranted('ROLE_ADMIN')) {
            $reports = $entityManager->getRepository(Signalement::class)->findAll();
            
        } 
        
        elseif ($user instanceof Moderateur) {
            $categorie = $user->getCategory(); 

            if ($categorie === 'Personnage') {
                $reports = $signalementRepository->findByType('Personnage','Traité');
                dump($reports);
            } else if ($categorie === 'Utilisateur'){
                $reports = $signalementRepository->findByType('Utilisateur','Traité');
                dump($reports);
            }
        }

        return $this->render('admin/index.html.twig', [
            'reports' => $reports,
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
        $modoP->setRoles(['ROLE_MODERATOR']); // On lui donne son rôle
        $modoP->setCategory('Personnage');     // On remplit le champ spécifique à l'enfant
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
