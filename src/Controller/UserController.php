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
use Knp\Component\Pager\PaginatorInterface;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'user.admin', methods: ['GET', 'POST'])]
    public function index(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        PaginatorInterface $paginator
    ): Response {
        // Pagination des utilisateurs
        $query = $userRepository->createQueryBuilder('u')->getQuery();
        $users = $paginator->paginate($query, $request->query->getInt('page', 1), 3);

        $user = $this->getUser();
        $users_all = $userRepository->findAll();

        // Statistiques
        $totalUsers = $userRepository->count([]);
        $activeUsers = $userRepository->count(['isActive' => true]);
        $bannedUsers = $userRepository->count(['banned' => true]);

        // Données pour le graphique
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData = [10, 20, 30, 40, 50, 60]; // Exemple de données

        // Activités récentes
        $recentActivities = $userRepository->findRecentActivities(10);

        // Créez une nouvelle instance de l'entité User
        $us = new User();

        // Créez le formulaire
        $form = $this->createForm(UserType::class, $us);

        // Traitez la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez l'utilisateur en base de données
            $entityManager->persist($us);
            $entityManager->flush();

            // Redirigez vers la même page après la création
            return $this->redirectToRoute('user.admin');
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'bannedUsers' => $bannedUsers,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentActivities' => $recentActivities,
            'user' => $user,
            'users_all' => $users_all,
            'users'=>$users
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
        SluggerInterface $slugger,
        UserRepository $userRepository,
        PaginatorInterface $paginator
    ): Response {
        $referer = $request->headers->get('referer');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $totalUsers = $userRepository->count([]);
        $activeUsers = $userRepository->count(['isActive' => true]);
        $bannedUsers = $userRepository->count(['banned' => true]);
        $recentActivities = $userRepository->findRecentActivities(10);

        $query = $userRepository->createQueryBuilder('u')->getQuery();
        $users = $paginator->paginate($query, $request->query->getInt('page', 1), 3);
        $users_all = $userRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('pp')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed!');
                    return $this->redirect($referer);
                }

                $user->setPp('/uploads/' . $newFilename);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'bannedUsers' => $bannedUsers,
            'recentActivities' => $recentActivities,
            'users' => $users,
            'users_all' => $users_all,
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
    #[Route('/profile', name: 'profile')]
    public function profile(EntityManagerInterface $em,Request $request, SluggerInterface $slugger): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $originalCover=$user->getPp();
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('pp')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed!');
                    return $this->redirect($referer);
                }

                if ($user->getPp()) {
                    $oldFilePath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($user->getPp());
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                $user->setPp('/uploads/' . $newFilename);
            }else{
                $user->setPp($originalCover);
            }
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirect($referer);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

}