<?php

// src/Controller/VilleController.php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Entity\LieuCulturels;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Media;
class VilleController extends AbstractController
{
    #[Route('/admin/ville', name: 'ville_index', methods: ['GET', 'POST'])]
    public function index(VilleRepository $villeRepository, PaginatorInterface $paginator, Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $villes = $paginator->paginate(
            $villeRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('ville_index');
        }

        return $this->render('ville/listeville.html.twig', [
            'villes' => $villes,
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    #[Route('/front/ville', name: 'ville_index_front', methods: ['GET', 'POST'])]
    public function index2(VilleRepository $villeRepository, PaginatorInterface $paginator, Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $villes = $paginator->paginate(
            $villeRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('ville_index_front');
        }

        return $this->render('ville/listvillefront.html.twig', [
            'villes' => $villes,
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/ville/{id}', name: 'ville_show', methods: ['GET'])]
    public function show(Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Filtrer les lieux culturels par ville (en fonction de l'ID de la ville)
        $lieuCulturels = $entityManager
            ->getRepository(LieuCulturels::class)
            ->findBy(['ville' => $ville->getId()]); // Assurez-vous que la relation est définie
    
        return $this->render('ville/detailville.html.twig', [
            'ville' => $ville,
            'lieu_culturels' => $lieuCulturels,
            'user' => $user,
        ]);
    }

    #[Route('front/ville/{id}', name: 'ville_show_front', methods: ['GET'])]
    public function show2(Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Filtrer les lieux culturels par ville (en fonction de l'ID de la ville)
        $lieuCulturels = $entityManager
            ->getRepository(LieuCulturels::class)
            ->findBy(['ville' => $ville->getId()]); // Assurez-vous que la relation est définie
    
        return $this->render('ville/detailsvillefront.twig', [
            'ville' => $ville,
            'lieu_culturels' => $lieuCulturels,
            'user' => $user,
        ]);
    }
    

    #[Route('/admin/ville/{id}/edit', name: 'ville_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('ville_index');
        }

        return $this->render('ville/modifierville.html.twig', [
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/ville/{id}', name: 'ville_delete', methods: ['POST'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Vérifier le token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $ville->getId(), $request->request->get('_token'))) {
            // Récupérer les lieux culturels associés à cette ville
            $lieuxCulturels = $ville->getLieux();
    
            foreach ($lieuxCulturels as $lieu) {
                // Supprimer les médias associés à chaque lieu culturel
                $medias = $lieu->getMedia(); // Assurez-vous que la relation existe dans l'entité LieuCulturels
                foreach ($medias as $media) {
                    $entityManager->remove($media);
                }
    
                // Supprimer le lieu culturel
                $entityManager->remove($lieu);
            }
    
            // Supprimer la ville
            $entityManager->remove($ville);
            $entityManager->flush();
    
            // Rediriger vers l'index des villes
            return $this->redirectToRoute('ville_index');
        }
    
        // Si le token CSRF est invalide, lever une exception
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }
    
}