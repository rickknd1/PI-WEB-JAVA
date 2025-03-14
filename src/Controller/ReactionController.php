<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Reaction;
use App\Enum\ReactionChoise;
use App\Form\ReactionType;
use App\Repository\ReactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reaction')]
final class ReactionController extends AbstractController
{
    #[Route(name: 'app_reaction_index', methods: ['GET'])]
    public function index(ReactionRepository $reactionRepository): Response
    {
        return $this->render('reaction/index.html.twig.twig', [
            'reactions' => $reactionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reaction = new Reaction();
        $form = $this->createForm(ReactionType::class, $reaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reaction/new.html.twig', [
            'reaction' => $reaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reaction_show', methods: ['GET'])]
    public function show(Reaction $reaction): Response
    {
        return $this->render('reaction/show.html.twig', [
            'reaction' => $reaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reaction $reaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReactionType::class, $reaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reaction/edit.html.twig', [
            'reaction' => $reaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reaction_delete', methods: ['POST'])]
    public function delete(Request $request, Reaction $reaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reaction->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/post/{id}/like', name: 'app_like')]
    public function like(Post $post, EntityManagerInterface $em, UserRepository $userRepository, ReactionRepository $reactionRepository): Response
    {
        $user = $this->getUser();

        $existingReaction = $reactionRepository->findOneBy([
            'post' => $post,
            'user' => $user
        ]);

        if ($existingReaction) {
            $em->remove($existingReaction);
        } else {
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setPost($post);
            $reaction->setType(ReactionChoise::LIKE);

            $post->addReaction($reaction);

            $em->persist($reaction);
        }

        $em->flush();

        return $this->redirectToRoute('app_post_index');
    }
}
