<?php

namespace App\Controller;

use App\Entity\ChatRoomMembres;
use App\Entity\ChatRooms;
use App\Entity\MembreComunity;
use App\Entity\Messages;
use App\Form\ChatRoomsType;
use App\Repository\ChatRoomMembresRepository;
use App\Repository\ChatRoomsRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Event\MessageSentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ChatRoomsController extends AbstractController{
    #[Route('admin/chatrooms', name: 'chatrooms.index')]
    public function index(Request $request, ChatRoomsRepository $repository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $routeName = $request->attributes->get('_route');
        if ($routeName=== "chatrooms.index" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
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
        $routeName = $request->attributes->get('_route');
        if ($routeName=== "chatroom.edit" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
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
    #[Route('/chatroom/{id}/delete', name: 'chatroom.front.del', requirements: ['id'=>'\d+'])]
    public function delete(ChatRooms $chatroom, EntityManagerInterface $em,Request $request,MembreComunityRepository $membreComunityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $routeName = $request->attributes->get('_route');
        if ($routeName=== "chatroom.del" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
        $userComm = $membreComunityRepository->findByUserId($user->getId());
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($chatroom->getCommunity()->getId(), $moderatedCommIds) || in_array($chatroom->getCommunity()->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($chatroom->getCover()) {
                $oldCover = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($chatroom->getCover());
                if (file_exists($oldCover)) {
                    unlink($oldCover);
                }
            }
            $em->remove($chatroom);
            $em->flush();
            $this->addFlash('success', 'Chatroom deleted!');
            if ($routeName === 'chatroom.front.del'){
                return $this->redirect($referer);
            }else{
                return $this->redirectToRoute('chatrooms.index');
            }
        }else{
            return $this->redirectToRoute('access_denied');
        }

    }

    #[Route('/admin/chatroom/{slug}-{id}', name: 'chatroom.show', requirements: ['id'=>'\d+','slug'=>'[a-zA-Z0-9-]+'])]
    public function detail(ChatRoomsRepository $repository, string $slug, int $id,Request $request): Response
    {
        $user = $this->getUser();
        $routeName = $request->attributes->get('_route');
        if ($routeName=== "chatroom.show" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
        $chatroom = $repository->find($id);
        if (!$chatroom || $chatroom->getNom() !== $slug) {
            return $this->redirectToRoute('chatroom.show', ['id' => $chatroom->getId(), 'slug' => $chatroom->getNom()]);
        }
        return $this->render('chat_rooms/detail.html.twig', [
            'chatroom' => $chatroom,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/join/chat/{id}',name: 'join.chatroom', requirements: ['id'=>'\d+'])]
    public function joinChatRoom(ChatRooms $chatroom, EntityManagerInterface $em,Request $request): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $chatRoomMembre = new ChatRoomMembres();
        $chatRoomMembre->setChatroom($chatroom);
        $chatRoomMembre->setUser($user);
        $user->incrementPoints(10);
        $em->persist($chatRoomMembre);
        $em->flush();
        if ($referer) {
            return $this->redirect($referer);
        }else{
            return $this->redirectToRoute('access_denied');
        }

    }
    #[Route('/leave/chat/{id}', name: 'leave.chatroom', requirements: ['id' => '\d+'])]
    public function leaveChatRoom(ChatRooms $chatroom, EntityManagerInterface $em, Request $request): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $chatRoomMembre = $em->getRepository(ChatRoomMembres::class)->findOneBy([
            'chatRoom' => $chatroom,
            'user' => $user
        ]);

        if ($chatRoomMembre) {
            $em->remove($chatRoomMembre);
            $em->flush();
            $user->decrementPoints(10);
        }
        if ($referer) {
            return $this->redirectToRoute('chatroom.front');
        } else {
            return $this->redirectToRoute('access_denied');
        }
    }


    #[Route('/chat/{id}', name: 'chatroom.chat', requirements: ['id'=>'\d+'])]
    public function show(int $id,ChatRoomMembresRepository $chatRoomMembresRepository,ChatRoomsRepository $chatRoomsRepository,
                         MessagesRepository $messagesRepository): Response
    {

        $user = $this->getUser();
        $userchats = $chatRoomMembresRepository->findBy(['user' => $user]);
        $chat = $chatRoomsRepository->find($id);
        $messages = $messagesRepository->findBy(['chatRoom' => $chat], ['sentAt' => 'ASC']);
        $allmessages = $messagesRepository->findAll();
        return $this->render('chat_rooms/chat.html.twig', [
            'user' => $user,
            'userchats' => $userchats,
            'chat' => $chat,
            'messages' => $messages,
            'allmessages' => $allmessages,
        ]);
    }
    #[Route('/chat', name: 'chatroom.front')]
    public function showfront(ChatRoomMembresRepository $chatRoomMembresRepository,ChatRoomsRepository $chatRoomsRepository,
                         MessagesRepository $messagesRepository): Response
    {

        $user = $this->getUser();
        $userchats = $chatRoomMembresRepository->findBy(['user' => $user]);
        $allmessages = $messagesRepository->findAll();
        return $this->render('chat_rooms/chat_front.html.twig', [
            'user' => $user,
            'userchats' => $userchats,
            'allmessages' => $allmessages,
        ]);
    }

    #[Route('/message/{id}', name: 'send.msg', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function send(int $id,Request $request, ChatRoomsRepository $chatRoomsRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $chatRoom = $chatRoomsRepository->find($id);
        $content = $request->request->get('message');

        $message = new Messages();
        $message->setChatRoom($chatRoom);
        $message->setUser($user);
        $message->setContent($content);
        $message->setSentAt(new \DateTimeImmutable());
        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('chatroom.chat', ['id' => $id]);

    }
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function sendMessage(Request $request, int $chatRoomId,ChatRoomsRepository $chatRoomsRepository): Response
    {
        $message = new Messages();
        $message->setContent($request->request->get('message'))
            ->setUser($this->getUser())
            ->setChatRoom($this->$chatRoomsRepository->find($chatRoomId))
            ->setSentAt(new \DateTimeImmutable());

        $event = new MessageSentEvent($message);
        $this->eventDispatcher->dispatch($event, MessageSentEvent::NAME);

        return $this->redirectToRoute('chatroom.chat', ['id' => $chatRoomId]);
    }
}
