<?php

namespace App\Controller;

use App\Entity\LieuCulturels;
use App\Form\LieuCulturelsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use App\Entity\Media;
use App\Entity\MediaRepository;




#[Route('/lieu/culturel')]
class LieuCulturelController extends AbstractController
{
    #[Route('/', name: 'app_lieu_culturel_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les lieux culturels
        $lieuCulturels = $entityManager
            ->getRepository(LieuCulturels::class)
            ->findAll();

        // Afficher la liste des lieux culturels
        return $this->render('lieu_culturel/listeLieuCulturel.html.twig', [
            'lieu_culturels' => $lieuCulturels,
        ]);
    }

    #[Route('/ajouter', name: 'app_lieu_culturel_ajout', methods: ['GET', 'POST'])]
    public function ajout(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Récupérer l'ID de la ville depuis les paramètres GET
        $villeId = $request->query->get('ville_id');
    
        if (!$villeId) {
            // Si aucun ID de ville n'est fourni, vous pouvez rediriger ou afficher une erreur
            throw $this->createNotFoundException('Aucune ville sélectionnée.');
        }
    
        // Créer une nouvelle instance de LieuCulturels
        $lieuCulturel = new LieuCulturels();
    
        // Récupérer l'entité Ville associée
        $ville = $entityManager->getRepository(Ville::class)->find($villeId);
        if (!$ville) {
            // Si la ville n'existe pas, afficher une erreur
            throw $this->createNotFoundException('Ville non trouvée.');
        }
    
        // Associer la ville au lieu culturel
        $lieuCulturel->setVille($ville);
    
        // Créer le formulaire
        $form = $this->createForm(LieuCulturelsType::class, $lieuCulturel);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();
    
                // Déplacer le fichier dans le répertoire de stockage
                $coverFile->move(
                    $this->getParameter('covers_directory'),
                    $newFilename
                );
    
                // Enregistrer le nom du fichier dans l'entité
                $lieuCulturel->setCover($newFilename);
            }
    
            // Enregistrer l'entité en base de données
            $entityManager->persist($lieuCulturel);
            $entityManager->flush();
    
            // Rediriger vers la liste des lieux culturels
            return $this->redirectToRoute('ville_show', ['id' => $lieuCulturel->getVille()->getId()], Response::HTTP_SEE_OTHER);
        }
    
        // Afficher le formulaire d'ajout
        return $this->render('lieu_culturel/ajoutLieuCulturel.html.twig', [
            'lieu_culturel' => $lieuCulturel,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_lieu_culturel_detail', methods: ['GET'])]
    public function detail(LieuCulturels $lieuCulturel, EntityManagerInterface $entityManager): Response
    {
        // Filtrer les médias associés au lieu culturel
        $media = $entityManager
            ->getRepository(Media::class)
            ->findBy(['lieux' => $lieuCulturel->getId()]); // 'lieux' doit correspondre à la relation dans Media
    
        // Afficher les détails d'un lieu culturel
        return $this->render('lieu_culturel/detailLieuCulturel.html.twig', [
            'lieu_culturel' => $lieuCulturel,
            'media' => $media,
        ]);
    }

    #[Route('front/{id}', name: 'app_lieu_culturel_detail_front', methods: ['GET'])]
    public function detail2(LieuCulturels $lieuCulturel, EntityManagerInterface $entityManager): Response
    {
        // Filtrer les médias associés au lieu culturel
        $media = $entityManager
            ->getRepository(Media::class)
            ->findBy(['lieux' => $lieuCulturel->getId()]); // 'lieux' doit correspondre à la relation dans Media
    
        // Afficher les détails d'un lieu culturel
        return $this->render('lieu_culturel/detailsLieuCulturelfront.html.twig', [
            'lieu_culturel' => $lieuCulturel,
            'media' => $media,
        ]);
    }
    

    #[Route('/{id}/modifier', name: 'app_lieu_culturel_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, LieuCulturels $lieuCulturel, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Créer le formulaire de modification
        $form = $this->createForm(LieuCulturelsType::class, $lieuCulturel);
        $form->handleRequest($request);

        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();

                // Déplacer le fichier dans le répertoire de stockage
                $coverFile->move(
                    $this->getParameter('covers_directory'), // Utilisation du paramètre
                    $newFilename
                );

                // Enregistrer le nom du fichier dans l'entité
                $lieuCulturel->setCover($newFilename);
            }

            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Rediriger vers la liste des lieux culturels
            return $this->redirectToRoute('ville_show', ['id' => $lieuCulturel->getVille()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Afficher le formulaire de modification
        return $this->render('lieu_culturel/modifierLieuCulturel.html.twig', [
            'lieu_culturel' => $lieuCulturel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_lieu_culturel_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, LieuCulturels $lieuCulturel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $lieuCulturel->getId(), $request->request->get('_token'))) {
            // Supprimer les médias associés
            $mediaRepository = $entityManager->getRepository(Media::class);
            $mediaList = $mediaRepository->findBy(['lieux' => $lieuCulturel->getId()]);
            foreach ($mediaList as $media) {
                $entityManager->remove($media);
            }

            // Supprimer le fichier image associé au lieu culturel
            if ($lieuCulturel->getCover()) {
                $coverPath = $this->getParameter('covers_directory') . '/' . $lieuCulturel->getCover();
                if (file_exists($coverPath)) {
                    unlink($coverPath);
                }
            }

            // Supprimer le lieu culturel
            $entityManager->remove($lieuCulturel);
            $entityManager->flush();

            return $this->redirectToRoute('ville_show', ['id' => $lieuCulturel->getVille()->getId()], Response::HTTP_SEE_OTHER);
        }

        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

}