<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Personnage;
use App\Entity\Histoire;
use App\Form\CommentaireType;
use App\Form\PersonnageType;
use App\Form\ReponseType;
use App\Repository\ApparenceRepository;
use App\Repository\HistoireRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/personnage')]
final class PersonnageController extends AbstractController
{
    #[Route(name: 'app_personnage_index', methods: ['GET'])]
    public function index(PersonnageRepository $personnageRepository, HistoireRepository $histoireRepository, EntityManagerInterface $entityManager): Response
    {   
        return $this->render('personnage/index.html.twig', [
            'personnages' => $personnageRepository->findBy(
            ['utilisateur' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_personnage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $personnage = new Personnage();
        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnage->setUtilisateur($this->getUser());
            $entityManager->persist($personnage);
            $entityManager->flush();

            return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('personnage/new.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_personnage_show', methods: ['GET','POST'])]
    public function show(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    {   
        // Trie des histoires par ordre d'affichage (Valeur nulle à la fin)
        $histoires = $personnage->getHistoires()->toArray();
        usort($histoires, function($a, $b) { //Trie un tableau en utilisant une fonction de comparaison
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });

        $apparences = $personnage->getApparences()->toArray();
        usort($apparences, function($a, $b) { 
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });

        // Commentaires 
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $formReponse = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
        $formReponse->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commentaireParent = null;
            $commentaireID = $request->request->getInt('commentaireID');

            // Si un ID de commentaire parent est fourni, c'est une réponse
            if ($commentaireID) {
                $commentaireParent = $entityManager->getRepository(Commentaire::class)->find($commentaireID);
                if ($commentaireParent) {
                    $commentaire->setCommentaire($commentaireParent);
                }
            }   
            if ($commentaireParent) {
                $commentaire->setCommentaire($commentaireParent);
                $commentaireParent->addReponse($commentaire);
            }
            $commentaire->setPersonnage($personnage);
            $commentaire->setUtilisateur($this->getUser());
            $commentaire->setDate(new \DateTimeImmutable());
            $entityManager->persist($commentaire);
            $entityManager->flush();
            return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);

        }

        $personnagesPublics = $entityManager->getRepository(Personnage::class)
            ->createQueryBuilder('p')
            ->andWhere('p.isPublic = :public')
            ->andWhere('p.id != :id')
            ->setParameter('public', true)
            ->setParameter('id', $personnage->getId())
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
       

        return $this->render('personnage/show.html.twig', [
            'personnage' => $personnage,
            'personnagesPublics' => $personnagesPublics,
            'form'=>$form,
            'formR'=>$formReponse,
            'apparences'=>$apparences,
            'histoires'=>$histoires,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'app_personnage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('personnage/edit.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_personnage_delete', methods: ['POST'])]
    public function delete(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$personnage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($personnage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_personnage_index', [], Response::HTTP_SEE_OTHER);
    }


//================================= Méthodes persos =================================//

    #[Route('/personnageLie', name: 'app_add_persoLie', methods: ['POST'])]
    public function addPersoLie(Request $request, EntityManagerInterface $em): Response
    {
        $personnageId = $request->request->getInt('personnageId'); // ID du personnage actuel
        $persoLieId = $request->request->getInt('persoLies');     // ID du personnage lié sélectionné

        $personnage = $em->getRepository(Personnage::class)->find($personnageId);
        $personnageLi = $em->getRepository(Personnage::class)->find($persoLieId);

        if ($personnage && $personnageLi) {
            $personnage->addPersoLy($personnageLi);
            $em->persist($personnage);
            $em->flush();
        }

        return $this->redirectToRoute('app_personnage_show', ['id' => $personnageId]);
    }




    //Permet de reorganiser la liste si l'utilisateur change l'ordre d'affichage 
    public function reorganisation(Personnage $personnage, HistoireRepository $histoireRepository, EntityManagerInterface $em): void
    {
        // normalise les ordres existants et retire les nulls en fin
        $items = $histoireRepository
            ->createQueryBuilder('e')
            ->andWhere('e.personnage = :p')
            ->setParameter('p', $personnage)
            ->orderBy('e.ordreAffichage', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        $order = 1;
        foreach ($items as $item) {
            $item->setOrdreAffichage($order);
            $order++;
        }

        $em->flush();
    }

    //Jsp comment faire une fonction pour les deux donc copier coller :(
    public function reorganisationA(Personnage $personnage, HistoireRepository $histoireRepository, EntityManagerInterface $em): void
    {
        // normalise les ordres existants et retire les nulls en fin
        $items = $histoireRepository
            ->createQueryBuilder('e')
            ->andWhere('e.personnage = :p')
            ->setParameter('p', $personnage)
            ->orderBy('e.ordreAffichage', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        $order = 1;
        foreach ($items as $item) {
            $item->setOrdreAffichage($order);
            $order++;
        }

        $em->flush();
    }






    #[Route('/{personnageId}/histoire/{histoireId}/ordre', name: 'app_personnage_histoire_ordre', methods: ['POST'])]
    public function updateHistoireOrdre(
        int $personnageId,
        int $histoireId,
        Request $request,
        PersonnageRepository $personnageRepository,
        HistoireRepository $histoireRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $personnage = $personnageRepository->findOneBy(['id' => $personnageId]);
        if (!$personnage) {
            throw $this->createNotFoundException('Personnage not found');
        }

        $histoire = $histoireRepository->find($histoireId);
        if (!$histoire) {
            throw $this->createNotFoundException('Histoire not found');
        }

        // Récupère toutes les histoires du personnage, ordonnées par ordreAffichage (nulls en fin)
        $all = $histoireRepository
            ->createQueryBuilder('e')
            ->andWhere('e.personnage = :p')
            ->setParameter('p', $personnage)
            ->orderBy('e.ordreAffichage', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        // retire l'élément courant de la liste si présent
        $list = [];
        foreach ($all as $it) {
            if ($it->getId() !== $histoire->getId()) {
                $list[] = $it;
            }
        }

        // calcule la position demandée et la borne entre 1 et count(list)+1
        $requested = $request->request->getInt('ordreAffichage', 1);
        $maxPos = count($list) + 1;
        $pos = max(1, min($requested, $maxPos)); // position 1..maxPos

        // insère l'élément à la position voulue (index pos-1)
        array_splice($list, $pos - 1, 0, [$histoire]);

        // réattribue des ordreAffichage séquentiels commençant à 1
        $i = 1;
        foreach ($list as $it) {
            $it->setOrdreAffichage($i);
            $entityManager->persist($it);
            $i++;
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_personnage_show', ['id' => $personnageId], Response::HTTP_SEE_OTHER);
    }



    // Pareil elle est longue en plus celle là ._.
    #[Route('/{personnageId}/apparence/{apparenceId}/ordre', name: 'app_personnage_apparence_ordre', methods: ['POST'])]
    public function updateApparenceOrdre(
        int $personnageId,
        int $apparenceId,
        Request $request,
        PersonnageRepository $personnageRepository,
        ApparenceRepository $apparenceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $personnage = $personnageRepository->find($personnageId);
        if (!$personnage) {
            throw $this->createNotFoundException('Personnage not found');
        }

        $apparence = $apparenceRepository->find($apparenceId);
        if (!$apparence) {
            throw $this->createNotFoundException('Cette description n\'existe pas. ');
        }

        $all = $apparenceRepository
            ->createQueryBuilder('e')
            ->andWhere('e.personnage = :p')
            ->setParameter('p', $personnage)
            ->orderBy('e.ordreAffichage', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        $list = [];
        foreach ($all as $it) {
            if ($it->getId() !== $apparence->getId()) {
                $list[] = $it;
            }
        }

        $requested = $request->request->getInt('ordreAffichage', 1);
        $maxPos = count($list) + 1;
        $pos = max(1, min($requested, $maxPos)); // position 1..maxPos

        array_splice($list, $pos - 1, 0, [$apparence]);

        $i = 1;
        foreach ($list as $it) {
            $it->setOrdreAffichage($i);
            $entityManager->persist($it);
            $i++;
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_personnage_show', ['id' => $personnageId], Response::HTTP_SEE_OTHER);
    }

}




