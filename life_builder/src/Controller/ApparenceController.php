<?php

namespace App\Controller;

use App\Entity\Apparence;
use App\Entity\Personnage;
use App\Form\ApparenceType;
use App\Repository\ApparenceRepository;
use App\Repository\PersonnageRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/apparence')]
final class ApparenceController extends AbstractController
{
    // #[Route(name: 'app_apparence_index', methods: ['GET'])]
    // public function index(ApparenceRepository $apparenceRepository): Response
    // {
    //     return $this->render('apparence/index.html.twig', [
    //         'apparences' => $apparenceRepository->findAll(),
    //     ]);
    // }
    #[Route(path:'/apparences/{id}', name: 'app_apparence_index_personnage', methods: ['GET'])]
    public function index(ApparenceRepository $apparenceRepository, int $id): Response
    {
        $personnageApparences = $apparenceRepository->findBy(['personnage' => $id]);
        return $this->render('apparence/index.html.twig', [
            'apparences' => $personnageApparences,
            'id'=>$id,
        ]);
    }

    // #[Route('/new/{id}', name: 'app_apparence_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, PersonnageRepository $personnageRepository, EntityManagerInterface $entityManager,int $id): Response
    // {
    //     $apparence = new Apparence();
    //     $form = $this->createForm(ApparenceType::class, $apparence);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $personnage=$personnageRepository->findOneBy(['id' => $id]);            
    //         $apparence->setPersonnage($personnage);
    //         $entityManager->persist($apparence);
    //         $entityManager->flush();
    //         $this->reorganisation($entityManager);

    //         return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('apparence/new.html.twig', [
    //         'apparence' => $apparence,
    //         'form' => $form,
            
    //     ]);
    // }

    #[Route('/{id}', name: 'app_apparence_show', methods: ['GET'])]
    public function show(Apparence $apparence): Response
    {
        return $this->render('apparence/show.html.twig', [
            'apparence' => $apparence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_apparence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Apparence $apparence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApparenceType::class, $apparence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('images')->getData();
            
            $imagesArray = $apparence->getImages() ?? [];
            if ($files) {
                foreach ($files as $file) {
                    $newFilename = uniqid().'.'.$file->guessExtension();

                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/apparences',
                        $newFilename
                    );
                    $imagesArray[] = $newFilename;
                }
            }
            $apparence->setImages($imagesArray);
            

            $apparence->setModifiedAt(new DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_apparence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('apparence/edit.html.twig', [
            'apparence' => $apparence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_apparence_delete', methods: ['POST'])]
    public function delete(Request $request, Apparence $apparence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apparence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($apparence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_apparence_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('{id}/apparence/', name: 'app_show_apparence', methods: ['GET','POST'])]
    public function showApparence(Personnage $personnage, Request $request,EntityManagerInterface $entityManager, ApparenceRepository $apparenceRepository ): Response
    {   
        $apparences=$personnage->getApparences()->toArray();
;
        $apparence = new Apparence();
        $form = $this->createForm(ApparenceType::class, $apparence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {          
            $apparence->setPersonnage($personnage);
            $entityManager->persist($apparence);

            $files = $form->get('images')->getData();
            
            $imagesArray = $apparence->getImages() ?? [];
            if ($files) {
                foreach ($files as $file) {
                    $newFilename = uniqid().'.'.$file->guessExtension();

                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/apparences',
                        $newFilename
                    );
                    $imagesArray[] = $newFilename;
                }
            }
            $apparence->setImages($imagesArray);

            $apparence->setCreatedAt(new DateTimeImmutable());
            $apparence->setModifiedAt(new DateTimeImmutable());
            $entityManager->flush();
            $this->reorganisation($entityManager);

            return $this->redirectToRoute('app_show_apparence', ['id'=>$personnage->getId()], Response::HTTP_SEE_OTHER);
        }

        $query = $request->query->get('search');

        if ($query) {
            $apparences = $apparenceRepository->findByKeyword($query, $personnage->getId());
        }


        usort($apparences, function($a, $b) { 
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });

        return $this->render('personnage/apparence.html.twig', [
            'apparences' => $apparences,
            'personnage' => $personnage,
            'form'=>$form,
        ]);    
    }

    //================================= Méthodes persos =================================//

//Permet de reorganiser la liste si l'utilisateur change l'ordre d'affichage
    public function reorganisation(EntityManagerInterface $em): void
    {
        $items = $em->getRepository(Apparence::class)
            ->createQueryBuilder('e')
            ->orderBy('e.ordreAffichage', 'ASC')
            ->getQuery()
            ->getResult();

        $order = 1;
        foreach ($items as $item) {
            $item->setOrdreAffichage($order);
            $order++;
        }

        $em->flush();
    }

}


