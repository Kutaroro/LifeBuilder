<?php

namespace App\Controller;

use App\Entity\Histoire;
use App\Entity\Personnage;
use App\Form\HistoireType;
use App\Repository\HistoireRepository;
use App\Repository\PersonnageRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/histoire')]
final class HistoireController extends AbstractController
{
    // #[Route(name: 'app_histoire_index', methods: ['GET'])]
    // public function index(HistoireRepository $histoireRepository, int $id): Response
    // {
    //     return $this->render('histoire/index.html.twig', [
    //         'histoires' => $histoireRepository->findBy(['id' => $id]),
    //     ]);
    // }
    #[Route(path:'/histoires/{id}', name: 'app_histoire_index_personnage', methods: ['GET'])]
    public function index(HistoireRepository $histoireRepository, int $id): Response
    {
        $personnageHistoires = $histoireRepository->findBy(['personnage' => $id]);
        return $this->render('histoire/index.html.twig', [
            'histoires' => $personnageHistoires,
            'id'=>$id,
        ]);
    }

    // #[Route('/new/{id}', name: 'app_histoire_new', methods: [ 'POST'])]
    // public function new(Request $request, PersonnageRepository $personnageRepository, EntityManagerInterface $entityManager, int $id)
    // {
    //     $histoire = new Histoire();
    //     $form = $this->createForm(HistoireType::class, $histoire);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $personnage=$personnageRepository->findOneBy(['id' => $id]);
    //         $histoire->setPersonnage($personnage);
    //         $entityManager->persist($histoire);
    //         $entityManager->flush();
    //         $this->reorganisation($entityManager);

    //         return $this->redirectToRoute('app_show_histoire', ['id'=>$personnage->getId()], Response::HTTP_SEE_OTHER);
    //     }
    // }

    // #[Route('/{id}', name: 'app_histoire_show', methods: ['GET'])]
    // public function show(Histoire $histoire): Response
    // {
    //     return $this->render('histoire/show.html.twig', [
    //         'histoire' => $histoire,
    //     ]);
    // }

    #[Route('/{id}/edit/', name: 'app_histoire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Histoire $histoire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistoireType::class, $histoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $histoire->setModifiedAt(new DateTimeImmutable());

            $entityManager->flush();

            return $this->redirectToRoute('app_histoire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('histoire/edit.html.twig', [
            'histoire' => $histoire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_histoire_delete', methods: ['POST'])]
    public function delete(Request $request, Histoire $histoire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$histoire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($histoire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_personnage_show', ['id'=>$histoire->getPersonnage()->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('{id}/histoire/{category}', name: 'app_show_histoire', methods: ['GET','POST'])]
    public function showHistoire(Personnage $personnage, Request $request, EntityManagerInterface $entityManager, HistoireRepository $histoireRepository, ?string $category = null ): Response
    {   
        // Créer une histoire
        $histoire = new Histoire();
        $form = $this->createForm(HistoireType::class, $histoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $histoire->setPersonnage($personnage);
            $histoire->setCreatedAt(new DateTimeImmutable());
            $histoire->setModifiedAt(new DateTimeImmutable());
            $entityManager->persist($histoire);
            $entityManager->flush();
            $this->reorganisation($entityManager);

            return $this->redirectToRoute('app_show_histoire', ['id'=>$personnage->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($category) {
            $histoires = $entityManager->getRepository(Histoire::class)->findBy([
                'personnage' => $personnage,
                'categorie' => $category
            ]);
        } else {
            $histoires = $personnage->getHistoires()->toArray();
        }


        $categories = $entityManager->getRepository(Histoire::class)
                ->createQueryBuilder('h')
                ->select('DISTINCT h.categorie')
                ->where('h.personnage = :p')
                ->andWhere('h.categorie IS NOT NULL') 
                ->andWhere("h.categorie != ''")      
                ->setParameter('p', $personnage)
                ->getQuery()
                ->getResult();

        $query = $request->query->get('search');

        if ($query) {
            $histoires = $histoireRepository->findByKeyword($query, $personnage->getId());
        }


        usort($histoires, function($a, $b) { //Trie un tableau en utilisant une fonction de comparaison
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });

        return $this->render('personnage/histoire.html.twig', [
            'histoires' => $histoires,
            'personnage' => $personnage,
            'form'=>$form,
            'categories'=>$categories,
            'recherche' => $query
        ]);      
    }


//================================= Méthodes persos =================================//

//Permet de reorganiser la liste si l'utilisateur change l'ordre d'affichage
    public function reorganisation(EntityManagerInterface $em): void
    {
        $items = $em->getRepository(Histoire::class)
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