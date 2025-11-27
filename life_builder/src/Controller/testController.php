<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;


final class testController extends AbstractController
{ 
    #[Route('/test')]
    public function listIndex(): Response
    {
       return new Response('<h1>Bienvenue sur la page d\'accueil de mon site !</h1>');
        
    }
}

?>