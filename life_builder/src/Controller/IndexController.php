<?php

namespace App\Controller;

use App\Repository\PersonnageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class IndexController extends AbstractController{

    #[Route( name: 'app_home')]
    public function index(PersonnageRepository $personnageRepository): Response
    {
        $personnagesPopulaires = $personnageRepository->findTopPopulaires(5);
        $personnagesRecents = $personnageRepository->findBy([], ['createdAt' => 'DESC'], 5, 0);
        return $this->render('index/index.html.twig', [
           'persosPop' => $personnagesPopulaires,
           'persosRecents' => $personnagesRecents
        ]);
    }   


}