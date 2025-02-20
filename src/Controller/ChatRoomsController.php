<?php

namespace App\Controller;

use App\Entity\ChatRooms;
use App\Form\ChatRoomsType;
use App\Repository\ChatRoomsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ChatRoomsController extends AbstractController{
    #[Route('admin/chatrooms', name: 'chatrooms.index')]
    public function index(Request $request, ChatRoomsRepository $repository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $chatrooms = $repository->paginateChatRooms($page , $limit);
        $maxPage = ceil($chatrooms->count() / $limit);

        $chatroom = new ChatRooms();
        $form = $this->createForm(ChatRoomsType::class, $chatroom);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();
                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('chatrooms.index');
                }
                $chatroom->setCover('/uploads/' . $newFilename);
            }
            $chatroom->setCreatedAt(new \DateTimeImmutable());
            $em->persist($chatroom);
            $em->flush();
            $this->addFlash('success', 'Chatroom created!');
            return $this->redirectToRoute('chatrooms.index');
        }
        return $this->render('chat_rooms/index.html.twig', [
            'chatrooms' => $chatrooms,
            'form' => $form->createView(),
            'maxPage' => $maxPage,
            'page' => $page,
            'limit' => $limit,
            'user' => $user,
        ]);
    }

    #[Route('/admin/chatroom/{id}/edit', name: 'chatroom.edit', requirements: ['id'=>'\d+'])]
    public function edit(ChatRooms $chatroom, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChatRoomsType::class, $chatroom);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();
                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('chatrooms.index');
                }
                if ($chatroom->getCover()) {
                    $oldCover = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($chatroom->getCover());
                    if (file_exists($oldCover)) {
                        unlink($oldCover);
                    }
                }
                $chatroom->setCover('/uploads/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Chatroom edited!');
            return $this->redirectToRoute('chatrooms.index');
        }
        return $this->render('chat_rooms/edit.html.twig', [
            'chatroom' => $chatroom,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/chatroom/{id}/delete', name: 'chatroom.del', requirements: ['id'=>'\d+'])]
    public function delete(ChatRooms $chatroom, EntityManagerInterface $em): Response
    {
        if ($chatroom->getCover()) {
            $oldCover = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($chatroom->getCover());
            if (file_exists($oldCover)) {
                unlink($oldCover);
            }
        }
        $em->remove($chatroom);
        $em->flush();
        $this->addFlash('success', 'Chatroom deleted!');
        return $this->redirectToRoute('chatrooms.index');
    }

    #[Route('/admin/chatroom/{slug}-{id}', name: 'chatroom.show', requirements: ['id'=>'\d+','slug'=>'[a-zA-Z0-9-]+'])]
    public function detail(ChatRoomsRepository $repository, string $slug, int $id): Response
    {
        $chatroom = $repository->find($id);
        if (!$chatroom || $chatroom->getNom() !== $slug) {
            return $this->redirectToRoute('chatroom.show', ['id' => $chatroom->getId(), 'slug' => $chatroom->getNom()]);
        }
        return $this->render('chat_rooms/detail.html.twig', [
            'chatroom' => $chatroom,
            'user' => $this->getUser(),
        ]);
    }
}
