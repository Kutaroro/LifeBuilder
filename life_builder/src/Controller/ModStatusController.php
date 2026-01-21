<?php

namespace App\Controller;

use App\Entity\ModStatus;
use App\Form\ModStatusType;
use App\Repository\ModStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mod/status')]
final class ModStatusController extends AbstractController
{
    #[Route(name: 'app_mod_status_index', methods: ['GET'])]
    public function index(ModStatusRepository $modStatusRepository): Response
    {
        return $this->render('mod_status/index.html.twig', [
            'mod_statuses' => $modStatusRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mod_status_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $modStatus = new ModStatus();
        $form = $this->createForm(ModStatusType::class, $modStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($modStatus);
            $entityManager->flush();

            return $this->redirectToRoute('app_mod_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mod_status/new.html.twig', [
            'mod_status' => $modStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mod_status_show', methods: ['GET'])]
    public function show(ModStatus $modStatus): Response
    {
        return $this->render('mod_status/show.html.twig', [
            'mod_status' => $modStatus,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mod_status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ModStatus $modStatus, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModStatusType::class, $modStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mod_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mod_status/edit.html.twig', [
            'mod_status' => $modStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mod_status_delete', methods: ['POST'])]
    public function delete(Request $request, ModStatus $modStatus, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$modStatus->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($modStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mod_status_index', [], Response::HTTP_SEE_OTHER);
    }
}
