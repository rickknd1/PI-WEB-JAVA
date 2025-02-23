<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController{
    #[Route('/admin/user',name: 'user.admin', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository,Request $request,EntityManagerInterface $entityManager): Response
    {
        $users = $userRepository->findAll();

        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'attr' => ['novalidate' => 'novalidate'],
        ]);        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();


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

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();


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

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user.admin', [], Response::HTTP_SEE_OTHER);
    }
}
