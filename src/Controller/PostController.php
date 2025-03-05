<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\CommunityRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VisitorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository,CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        $comments = $commentRepository->findAll();
        return $this->render('post/feed.html.twig', [
            'posts' => $postRepository->findAll(),
            'comments' => $comments,
            'user' => $user
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('file')->getData();

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
                    return $this->redirectToRoute('app_post_new');
                }

                $post->setFile('/uploads/' . $newFilename);
            }
            $post->setUser($user);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpdateAt(new \DateTimeImmutable());
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post créé avec succès !');

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/post/{id}/share', name: 'app_share')]
    public function share(Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $sharedPost = new Post();
        $sharedPost->setUser($user);
        $sharedPost->setContent($post->getContent()); // Copier le contenu du post original
        $sharedPost->setSharedFrom($post);
        $sharedPost->setCreatedAt(new \DateTimeImmutable());

        $em->persist($sharedPost);
        $em->flush();

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, UserRepository $userRepository, CommunityRepository $groupRepository, PostRepository $pageRepository): JsonResponse
    {
        $query = $request->query->get('q');

        if (!$query) {
            return new JsonResponse([]);
        }

        // Recherche dans Users, Groups et Pages
        $users = $userRepository->searchByName($query);
        $groups = $groupRepository->searchByName($query);
        $pages = $pageRepository->searchByName($query);

        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'type' => 'Friend',
                'name' => $user->getFullName(),
                'image' => '/uploads/avatars/' . $user->getAvatar(),
            ];
        }

        foreach ($groups as $group) {
            $results[] = [
                'type' => 'Group',
                'name' => $group->getName(),
                'image' => '/uploads/groups/' . $group->getImage(),
            ];
        }

        foreach ($pages as $page) {
            $results[] = [
                'type' => 'Page',
                'name' => $page->getName(),
                'image' => '/uploads/pages/' . $page->getImage(),
            ];
        }

        return new JsonResponse($results);
    }

    #[Route('/recommendations', name: 'post_recommendations')]
    public function recommendations(PostRepository $postRepository, Security $security): Response
    {
        $user = $security->getUser();
        $recommendedPosts = $postRepository->findRecommendedPosts($user);

        return $this->render('post/recommendations.html.twig', [
            'recommendedPosts' => $recommendedPosts,
        ]);
    }

}
