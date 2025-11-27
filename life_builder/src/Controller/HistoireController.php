<?php

namespace App\Controller;

use App\Entity\Histoire;
use App\Form\HistoireType;
use App\Repository\HistoireRepository;
use App\Repository\PersonnageRepository;
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

    #[Route('/new/{id}', name: 'app_histoire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PersonnageRepository $personnageRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $histoire = new Histoire();
        $form = $this->createForm(HistoireType::class, $histoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnage=$personnageRepository->findOneBy(['id' => $id]);
            $histoire->setPersonnage($personnage);
            $entityManager->persist($histoire);
            $entityManager->flush();
            $this->reorganisation($entityManager);

            return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('histoire/new.html.twig', [
            'histoire' => $histoire,
            'form' => $form,
            'id'=>$id,
        ]);
    }

    #[Route('/{id}', name: 'app_histoire_show', methods: ['GET'])]
    public function show(Histoire $histoire): Response
    {
        return $this->render('histoire/show.html.twig', [
            'histoire' => $histoire,
        ]);
    }

    #[Route('/{id}/edit/', name: 'app_histoire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Histoire $histoire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistoireType::class, $histoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->redirectToRoute('app_histoire_index', [], Response::HTTP_SEE_OTHER);
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