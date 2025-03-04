<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'user.admin', methods: ['GET', 'POST'])]
    public function index(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $users = $userRepository->findAll();

        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'attr' => ['novalidate' => 'novalidate'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleProfilePicture($form, $user, $slugger);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleProfilePicture($form, $user, $slugger);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleProfilePicture($form, $user, $slugger);

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->getPp()) {
                $oldFilePath = $this->getParameter('profile_pictures_directory') . '/' . $user->getPp();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }

        return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode pour gérer l'upload de la photo de profil
     */
    private function handleProfilePicture($form, User $user, SluggerInterface $slugger): void
    {
        /** @var UploadedFile $pp */
        $pp = $form->get('pp')->getData();

        // Si une image a été uploadée
        if ($pp) {
            // Supprimer l'ancienne photo si elle existe et qu'on est en édition
            if ($user->getPp()) {
                $oldFilePath = $this->getParameter('profile_pictures_directory') . '/' . $user->getPp();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $originalFilename = pathinfo($pp->getClientOriginalName(), PATHINFO_FILENAME);
            // Sécurisation du nom de fichier
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $pp->guessExtension();

            // Déplacer le fichier dans le répertoire où sont stockées les photos de profil
            try {
                $pp->move(
                    $this->getParameter('profile_pictures_directory'),
                    $newFilename
                );

                // Mettre à jour l'entité avec le nom du fichier
                $user->setPp($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l"upload de l"image');
            }
        }
    }
    #[Route('/admin/user/{id}/ban', name: 'user_ban')]
    public function banUser(User $user, EntityManagerInterface $em): Response
    {
        // Vérifiez que l'utilisateur connecté a les droits pour bannir
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        // Bannir l'utilisateur
        $user->setBanned(true);
        $em->flush();

        $this->addFlash('success', 'L\'utilisateur a été banni avec succès.');
        return $this->redirectToRoute('user.admin'); // Redirigez vers la liste des utilisateurs
    }

}