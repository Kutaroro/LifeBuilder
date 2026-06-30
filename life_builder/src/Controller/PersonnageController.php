<?php

namespace App\Controller;

use App\Entity\Apparence;
use App\Entity\Commentaire;
use App\Entity\Personnage;
use App\Entity\Histoire;
use App\Entity\Utilisateur;
use App\Form\CommentaireType;
use App\Form\PersonnageType;
use App\Form\ReponseType;
use App\Repository\ApparenceRepository;
use App\Repository\HistoireRepository;
use App\Repository\PersonnageRepository;
use App\Repository\UtilisateurRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/personnage')]
final class PersonnageController extends AbstractController
{   
    private $personnageRepository;
    private $histoireRepository;
    public function __construct(PersonnageRepository $personnageRepository, HistoireRepository $histoireRepository)
    {
        $this->personnageRepository = $personnageRepository;
        $this->histoireRepository = $histoireRepository;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/index/{id}', name: 'app_personnage_index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(
        Utilisateur $utilisateur,
        Request $request
    ): Response {   
        $search = $request->query->get('search');
        $categorie = $request->query->get('category');
        $tag = $request->query->get('tag');

        
        $currentUser = $this->getUser(); 

        // On récupère les personnages de l'utilisateur. 
        // Pour le moment tous les personnages sont récupérés,
        // mais par la suite ceux qui sont supprimés seront ignoré
        $personnages = $this->personnageRepository->findByFilters(
            $utilisateur->getId(), 
            $currentUser ? $currentUser->getId() : null,
            $search, 
            $categorie, 
            $tag
        );    
        
        $allPersos = $this->personnageRepository->findBy(['utilisateur' => $utilisateur]);
        $categoriesDispo = [];
        $tagsDispo = [];

        // On récuprèe tous les tags et catégories des personnages.
        foreach ($allPersos as $p) {
            $categoriesDispo = array_merge($categoriesDispo, $p->getCategories() ?? []);
            $tagsDispo = array_merge($tagsDispo, $p->getTags() ?? []);
        }

        return $this->render('personnage/index.html.twig', [
            'personnages' => $personnages,
            'categories' => array_unique($categoriesDispo),
            'tags' => array_unique($tagsDispo),
            'utilisateur' => $utilisateur,
            'activeCategory' => $categorie,
            'activeTag' => $tag
        ]);
    }

    #[Route('/catalogue', name: 'app_personnage_catalogue', methods: ['GET'])]
    public function catalogue(Request $request): Response
    {   

        $search = $request->query->get('search');
        $categorie = $request->query->get('category');
        $tag = $request->query->get('tag');
        $personnagesPublics = $this->personnageRepository->findAllPublicByFilters($search, $categorie, $tag);      


        return $this->render('personnage/catalogue.html.twig', [
            'personnages' => $personnagesPublics,
            'utilisateur' => $this->getUser(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_personnage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {   
        $personnage = new Personnage();
        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnage->setUtilisateur($this->getUser());
            $file=$form->get('image')->getData();

            //gestion des images
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/personnages',
                    $newFilename
                );

                $personnage->setImage($newFilename);
            }
            $files = $form->get('imagesSecondaires')->getData();
            
            $imagesArray = $personnage->getImagesSecondaires() ?? [];
            if ($files) {
                foreach ($files as $file) {
                    $newFilename = uniqid().'.'.$file->guessExtension();

                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/personnages',
                        $newFilename
                    );
                    $imagesArray[] = $newFilename;
                }
            }
            $personnage->setImagesSecondaires($imagesArray);
            $personnage->setCreatedAt(new DateTimeImmutable());
            $personnage->setModifiedAt(new DateTimeImmutable());

            //récupération categories and tag, transformation en array 
            $catData = $form->get('categories_hidden')->getData();
            if ($catData) {
                $personnage->setCategories(array_filter(array_map('trim', explode(',', $catData))));
            }

            $tagData = $form->get('tags_hidden')->getData();
            if ($tagData) {
                $personnage->setTags(array_filter(array_map('trim', explode(',', $tagData))));
            }

            $entityManager->persist($personnage);
            $entityManager->flush();
            return $this->redirectToRoute('app_personnage_index', ['id' => $personnage->getUtilisateur()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('personnage/new.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    #[Route('/non',name:"app_non")] // A effacer mais verifier qu'il n'y ai plus de code qui utilise ça sans lever d'exception
    public function non(){

        return ("NON");
    }
    
    #[Route('/{id}/{category}', name: 'app_personnage_show', methods: ['GET','POST'])]
    public function show(Request $request, Personnage $personnage, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, ?string $category = null): Response
    {   
        $user= $this->getUser();
        if ($user !== $personnage->getUtilisateur() && !$personnage->IsPublic()){
            return $this->redirectToRoute('app_non');
        }

        // Trie des histoires par ordre d'affichage (Valeur nulle à la fin)

        if ($category) {
            $histoires = $entityManager->getRepository(Histoire::class)->findBy([
                'personnage' => $personnage,
                'categorie' => $category
            ], ['id' => 'DESC'], 4, 0);
        } else {
            $histoires = $entityManager->getRepository(Histoire::class)->findBy(['personnage' => $personnage,], ['id' => 'DESC'], 4, 0);
        }

        $apparences = $entityManager->getRepository(Apparence::class)->findBy(['personnage' => $personnage,], ['id' => 'DESC'], 4, 0);


        $commentaires= $personnage->getCommentaires()->toArray();

        // Commentaire
        $commentaire = new Commentaire();
        $form = $formFactory->createNamed('base_comment', CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setPersonnage($personnage);
            $commentaire->setUtilisateur($this->getUser());
            $commentaire->setDate(new \DateTimeImmutable());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_personnage_show', ['id' => $personnage->getId()]);
        }

        // Repo,nse 
        $reponseObj = new Commentaire();
        $formReponse = $formFactory->createNamed('reply_comment', CommentaireType::class, $reponseObj);
        $formReponse->handleRequest($request);

        if ($formReponse->isSubmitted() && $formReponse->isValid()) {
            //$parentID = $request->request->get('commentaireID'); 
            $parentID = $request->request->get('parent_id');

            if (!$parentID) {
                $this->addFlash('error', 'Action impossible : commentaire parent introuvable.');
                return $this->redirectToRoute('app_personnage_show', ['id' => $personnage->getId()]);
            }
            $parent = $entityManager->getRepository(Commentaire::class)->find($parentID);
            
            if ($parent) {
                $mentionnedUser = $parent->getUtilisateur();
                $reponseObj->setMentionedUtilisateur($mentionnedUser);
                $reponseObj->setCommentaire($parent);
                $reponseObj->setPersonnage($personnage);
                $reponseObj->setUtilisateur($this->getUser());
                $reponseObj->setDate(new \DateTimeImmutable());
                
                $entityManager->persist($reponseObj);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_personnage_show', ['id' => $personnage->getId()]);
        }
            

        $personnagesPublics = $this->personnageRepository->findPublicPersonnages($personnage->getId());
        $categories = $this->histoireRepository->findDistinctCategoriesByPersonnage($personnage);

        usort($histoires, function($a, $b) { //Trie un tableau en utilisant une fonction de comparaison
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });

        usort($apparences, function($a, $b) { 
            $av = $a->getOrdreAffichage() ?? PHP_INT_MAX;
            $bv = $b->getOrdreAffichage() ?? PHP_INT_MAX;
            return $av <=> $bv;
        });


        return $this->render('personnage/show.html.twig', [
            'personnage' => $personnage,
            'personnagesPublics' => $personnagesPublics,
            'form'=>$form,
            'formR'=>$formReponse,
            'apparences'=>$apparences,
            'histoires'=>$histoires,
            'commentaires'=>$commentaires,
            'categories'=>$categories,

        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit/informations', name: 'app_personnage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    {   
        $utilisateur = $personnage->getUtilisateur();
        $currentUser = $this->getUser();

       // Si on veut modifier un personnage qui n'est pas le sien et qu'on est pas admin, accès refusé
        if ($currentUser !== $utilisateur && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce personnage.");
        }

        // On s'assure que $tags est bien un array (même vide) avant de faire le implode
        $tags = $personnage->getTags();
        if (!is_array($tags)) {
            $tags = $tags ? [$tags] : []; // Si c'est une chaîne, on la met dans un tableau, sinon tableau vide
        }

        $categories = $personnage->getCategories();
        if (!is_array($categories)) {
            $categories = $categories ? [$categories] : [];
        }
        $form = $this->createForm(PersonnageType::class, $personnage, [
            'mapped_tags' => implode(',', $tags),
            'mapped_categories' => implode(',', $categories)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérer les chaînes envoyées par le JS et les retransformer en tableaux
            $tagsString = $form->get('tags_hidden')->getData();
            $catsString = $form->get('categories_hidden')->getData();

            // On nettoie et on transforme en array : "A, B" -> ["A", "B"]
            $personnage->setTags($tagsString ? explode(',', $tagsString) : []);
            $personnage->setCategories($catsString ? explode(',', $catsString) : []);


            $file=$form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/personnages',
                    $newFilename
                );

                $personnage->setImage($newFilename);
            }
            $files = $form->get('imagesSecondaires')->getData();
            
            $imagesArray = $personnage->getImagesSecondaires() ?? [];
            if ($files) {
                foreach ($files as $file) {
                    $newFilename = uniqid().'.'.$file->guessExtension();

                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/personnages',
                        $newFilename
                    );
                    $imagesArray[] = $newFilename;
                }
            }
            $personnage->setImagesSecondaires($imagesArray);
            $personnage->setModifiedAt(new DateTimeImmutable());

            $entityManager->persist($personnage);
            $entityManager->flush();
            return $this->redirectToRoute('app_personnage_index', ['id' => $personnage->getUtilisateur()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('personnage/edit.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    // #[IsGranted('ROLE_USER')]
    // #[Route('/{id}', name: 'app_personnage_delete', methods: ['POST'])]
    // public function delete(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    // {
    //     $utilisateur = $personnage->getUtilisateur();
    //     $currentUser = $this->getUser();

    //    // Si on veux supprimer un personnage qui n'est pas le sien et qu'on est pas admin, accès refusé
    //     if ($currentUser !== $utilisateur && !$this->isGranted('ROLE_ADMIN')) {
    //         throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer ce personnage.");
    //     }

    //     if ($this->isCsrfTokenValid('delete'.$personnage->getId(), $request->getPayload()->getString('_token'))) {
    //         $entityManager->remove($personnage);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('app_personnage_index', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
    // }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete/character/{id}', name: 'app_personnage_delete', methods: ['POST'])]
    public function delete(Request $request, Personnage $personnage, EntityManagerInterface $entityManager): Response
    {   
        
        $utilisateur = $personnage->getUtilisateur();
        $currentUser = $this->getUser();

        // Sécurité : Accès refusé si ce n'est pas son personnage et qu'on n'est pas admin
        if ($currentUser !== $utilisateur && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer ce personnage.");
        }

        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$personnage->getId(), $submittedToken)) {
            $personnage->setIsDeleted(true);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le personnage a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Le jeton de sécurité (CSRF) est invalide ou absent.');
        }

        return $this->redirectToRoute('app_personnage_index', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
    }


//================================= Méthodes persos =================================//

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/personnageLie/ajout', name: 'app_add_persoLie', methods: ['POST'])]
    public function addPersoLie(Request $request, EntityManagerInterface $em, Personnage $personnage): Response
    {   

        $utilisateur = $personnage->getUtilisateur();
        $currentUser = $this->getUser();

        // Sécurité : Accès refusé si ce n'est pas son personnage et qu'on n'est pas admin
        if ($currentUser !== $utilisateur && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce personnage.");
        }

        $persoLieId = $request->request->getInt('persoLies');     // ID du personnage lié sélectionné
        $personnageLi = $em->getRepository(Personnage::class)->findOneBy(['id' => $persoLieId]);

        if ($personnage && $personnageLi) {
            $personnage->addPersoLy($personnageLi);
            $em->persist($personnage);
            $em->flush();
        }

        return $this->redirectToRoute('app_show_persoLie', ['id' => $personnage->getID()]);
    }

    #[Route('{id}/personnageLie/', name: 'app_show_persoLie', methods: ['GET','POST'])]
    public function showPersoLie(Personnage $personnage, EntityManagerInterface $entityManager): Response
    {
        $personnagesPublics = $entityManager->getRepository(Personnage::class)
            ->createQueryBuilder('p')
            ->andWhere('p.isPublic = :public')
            ->andWhere('p.id != :id')
            ->setParameter('public', true)
            ->setParameter('id', $personnage->getId())
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('personnage/persoLie.html.twig', [
            'personnagesLies' => $personnage->getPersoLies(),
            'personnage' => $personnage,
            'personnagesPublics'=> $personnagesPublics
        ]);

    }
    //Permet de reorganiser la liste si l'utilisateur change l'ordre d'affichage 
    public function reorganisation(Personnage $personnage, EntityManagerInterface $em): void
    {
        // normalise les ordres existants et retire les nulls en fin
        $items = $this->histoireRepository
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
    public function reorganisationA(Personnage $personnage, EntityManagerInterface $em): void
    {
        // normalise les ordres existants et retire les nulls en fin
        $items = $this->histoireRepository
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
        EntityManagerInterface $entityManager
    ): Response {

        $personnage = $this->personnageRepository->findOneBy(['id' => $personnageId]);
        if (!$personnage) {
            throw $this->createNotFoundException('Personnage not found');
        }

        $histoire = $this->histoireRepository->find($histoireId);
        if (!$histoire) {
            throw $this->createNotFoundException('Histoire not found');
        }
        $currentUser = $this->getUser();
        // Si on veut modifier un personnage qui n'est pas le sien et qu'on est pas admin, accès refusé
        if ($currentUser !== $personnage->getUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce personnage.");
        }
        // Récupère toutes les histoires du personnage, ordonnées par ordreAffichage (nulls en fin)
        $all = $this->histoireRepository->findByPersonnageOrdered($personnage);

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
        // Attention au nombre et à la position des paramètres sinon
        // array_slice() peut modifier la liste de manière inattendue
        array_splice($list, $pos - 1, 0, [$histoire]);

        // réattribue des nouveaux ordreAffichage commençant à 1
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
        ApparenceRepository $apparenceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $personnage = $this->personnageRepository->find($personnageId);
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




