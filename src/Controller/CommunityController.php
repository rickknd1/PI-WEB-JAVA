<?php

namespace App\Controller;

use App\Entity\ChatRoomMembres;
use App\Entity\ChatRooms;
use App\Entity\Community;
use App\Entity\Events;
use App\Entity\MembreComunity;
use App\Entity\ParticipationEvent;
use App\Event\CommunityCreatedEvent;
use App\Form\ChatRoomsType;
use App\Form\CommunityType;
use App\Form\EventsType;
use App\Repository\CategoriesRepository;
use App\Repository\ChatRoomMembresRepository;
use App\Repository\ChatRoomsRepository;
use App\Repository\CommunityRepository;
use App\Repository\EventsRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\ParticipationEventRepository;
use App\Repository\VisitorsRepository;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CommunityController extends AbstractController{
    #[Route('/community', name: 'community.index.front')]
    #[Route('admin/community', name: 'community.index')]
    public function index(Request $request,EntityManagerInterface $em,
                          CommunityRepository $communityRepository,CategoriesRepository $categoriesRepository,MembreComunityRepository $membreComunityRepository,
                          SluggerInterface $slugger,MailerInterface $mailer,
                          EventDispatcherInterface $eventDispatcher): Response
    {
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('welcome');
        }
        if ($routeName=== "community.index" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }


        $userId = $user->getId();
        $userComm = $membreComunityRepository->findByUserId($userId);
        $userCommIds = array_map(fn($item) => $item->getCommunity()->getId(), $userComm);
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        $cats = $categoriesRepository->findAll();
        $communitiesFront = $communityRepository->findBy(['statut' => 1]);
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $communities = $communityRepository->paginateCommunity($page , $limit);
        $maxPage = ceil($communities->count() / $limit);

        $community = new Community();
        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
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
                    return $this->redirectToRoute('community.index');
                }

                $community->setCover('/uploads/' . $newFilename);
            }
            if ($routeName === 'community.index.front'){
                $community->setNbrMembre(1);
                $community->setStatut(0);
            }else{
                $community->setNbrMembre(0);
                $community->setStatut(1);
            }

            $community->setCreatedAt(new \DateTimeImmutable());

            $em->persist($community);
            $em->flush();
            $this->addFlash('success', 'Community created!');

            if ($routeName === 'community.index.front'){
                $membreComunity = new MembreComunity();
                $membreComunity->setIdUser($user);
                $membreComunity->setIdCommunity($community);
                $membreComunity->setStatus('owner');
                $membreComunity->setDateAdhesion(new \DateTime());

                $eventDispatcher->dispatch(new CommunityCreatedEvent($community, $user), CommunityCreatedEvent::NAME);

                $em->persist($membreComunity);
                $em->flush();
            }
            if ($routeName === 'community.index.front'){
                return $this->redirectToRoute('community.index.front');
            }else{
                return $this->redirectToRoute('community.index');
            }
        }

        if ($routeName === 'community.index.front'){
            return $this->render('community/index_Front.html.twig',
                [
                'communities' => $communities,
                'maxPage' => $maxPage,
                'page' => $page,
                'limit' => $limit,
                'cats' => $cats,
                'form' => $form->createView(),
                'communitiesFront'=>$communitiesFront,
                'user'=> $user,
                'userCommIds'=>$userCommIds,
                'moderatedCommIds' => $moderatedCommIds,
                'ownCommIds' => $ownCommIds,
            ]);

        }else{
            return $this->render('community/index.html.twig', [
                'communities' => $communities,
                'form' => $form->createView(),
                'maxPage' => $maxPage,
                'page' => $page,
                'limit' => $limit,
                'user'=> $user,
                'userComm'=>$userComm,
                'userCommIds'=>$userCommIds,

            ]);
        }

    }
    #[Route('admin/community/{id}/edit', name: 'community.edit' , requirements: ['id'=>'\d+'])]
    #[Route('community/{id}/edit', name: 'community.front.edit' , requirements: ['id'=>'\d+'])]
    public function edit(Request $request, Community $community, EntityManagerInterface $em, SluggerInterface $slugger,MembreComunityRepository $membreComunityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }

        $userId = $user->getId();

        $userComm = $membreComunityRepository->findByUserId($userId);
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($community->getId(), $moderatedCommIds) || in_array($community->getId(), $ownCommIds) ||in_array('ROLE_ADMIN', $user->getRoles())) {
            $form = $this->createForm(CommunityType::class, $community);
            $form->handleRequest($request);
            $originalCover = $community->getCover();
            if ($form->isSubmitted() && $form->isValid()) {
                $coverFile = $form->get('cover')->getData();
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
                        return $this->redirectToRoute('community.index');
                    }

                    if ($community->getCover()) {
                        $oldFilePath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($community->getCover());
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }

                    $community->setCover('/uploads/' . $newFilename);
                }else{
                    $community->setCover($originalCover);
                }
                $em->flush();
                $this->addFlash('success', 'Community updated!');
                if ($referer) {
                    return $this->redirect($referer);
                }
            }
        } else {
            return $this->redirectToRoute('access_denied');
        }
        if ($routeName==='community.edit'){
            return $this->render('community/edit.html.twig', [
                'community' => $community,
                'form' => $form->createView(),
                'user'=> $user,
                'cover' => $originalCover,
            ]);
        }else{
            return $this->render('community/edit.html.twig', [
                'community' => $community,
                'form' => $form->createView(),
                'user'=> $user,
                'cover' => $originalCover,
            ]);
        }

    }
    #[Route('admin/community/{id}/delete', name: 'community.del' , requirements: ['id'=>'\d+'])]
    #[Route('community/{id}/delete', name: 'community.front.del' , requirements: ['id'=>'\d+'])]
    public function delete(Request $request, Community $community, EntityManagerInterface $em, LoggerInterface $logger,MembreComunityRepository $membreComunityRepository): Response
    {
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if ($routeName=== "community.del" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }

        $userId = $user->getId();

        $userComm = $membreComunityRepository->findByUserId($userId);
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($community->getId(), $moderatedCommIds) || in_array($community->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($community->getCover()) {
                $coverPath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($community->getCover());
                if (file_exists($coverPath)) {
                    unlink($coverPath);
                }else{
                    $logger->error("File not found: " . $coverPath);
                }
            }
            $em->remove($community);
            $em->flush();
            $this->addFlash('success', 'Community deleted!');
            if ($routeName === 'community.front.del'){
                return $this->redirectToRoute('community.index.front');
            }else{
                return $this->redirectToRoute('community.index');
            }
        }else{
            return $this->redirectToRoute('access_denied');
        }


    }
    #[Route('/community/{id}/events', name: 'community.events' , requirements: ['id'=>'\d+'])]
    #[Route('/community/{id}/chat_rooms', name: 'community.detail', requirements: ['id' => '\d+'])]
    #[Route('/community/{id}/members', name: 'community.members', requirements: ['id' => '\d+'])]
    #[Route('/community/{id}', name: 'community.show', requirements: ['id' => '\d+'])]
    public function detail(Request $request,Community $community,EventsRepository $eventsRepository,EntityManagerInterface $em,
                           CommunityRepository $communityRepository,ChatRoomsRepository $chatRoomsRepository,MembreComunityRepository $membreComunityRepository,
                           QrCodeService $qrCodeService,SluggerInterface $slugger,ChatRoomMembresRepository $chatRoomMembresRepository,
                           ParticipationEventRepository $participationEventRepository
    ): Response
    {
        $qrCode = base64_encode($qrCodeService->generateQrCode($community));
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $userId = $user->getId();
        $userComm = $membreComunityRepository->findByUserId($userId);
        $userCommIds = array_map(fn($item) => $item->getCommunity()->getId(), $userComm);
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        $events = $eventsRepository->findBy(['id_community' => $community]);
        $communitys = $communityRepository->findAll();
        $chatRooms = $chatRoomsRepository->findBy(['community' => $community]);
        $membres = $membreComunityRepository->findBy(['community' => $community]);

        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
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
                    return $this->redirectToRoute('community.index');
                }

                if ($community->getCover()) {
                    $oldFilePath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($community->getCover());
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $community->setCover('/uploads/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Community updated!');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        $event = new Events();
        $form_event = $this->createForm(EventsType::class, $event);
        $form_event->handleRequest($request);
        if ($form_event->isSubmitted() && $form_event->isValid()) {
            $coverFile = $form_event->get('cover')->getData();
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
                    return $this->redirect($referer);
                }
                $event->setCover('/uploads/' . $newFilename);
            }
            $event->setIdCommunity($community);
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Event created!');
            return $this->redirect($referer);
        }

        $chatroom = new ChatRooms();
        $form_chat = $this->createForm(ChatRoomsType::class, $chatroom);
        $form_chat->handleRequest($request);
        if ($form_chat->isSubmitted() && $form_chat->isValid()) {
            $coverFile = $form_chat->get('cover')->getData();
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
                    return $this->redirect($referer);
                }
                $chatroom->setCover('/uploads/' . $newFilename);
            }
            $chatroom->setCreatedAt(new \DateTimeImmutable());
            $chatroom->setCommunity($community);

            $chatRoomMembre = new ChatRoomMembres();
            $chatRoomMembre->setChatRoom($chatroom);
            $chatRoomMembre->setUser($user);
            $em->persist($chatRoomMembre);

            $em->persist($chatroom);
            $em->flush();
            $this->addFlash('success', 'Chatroom created!');
            return $this->redirect($referer);
        }
        $userChats = $chatRoomMembresRepository->findBy(['user' => $user]);
        $userChatRoomIds = array_map(fn($userChat) => $userChat->getChatRoom()->getId(), $userChats);
        $userParticipation = $participationEventRepository->findBy(['user' => $user, 'type' => 'Participate']);
        $userParticipationIds = array_map(fn($userParticipation) => $userParticipation->getEvent()->getId(), $userParticipation);

        $userInterested = $participationEventRepository->findBy(['user' => $user, 'type' => 'Interested']);
        $userInterestedIds = array_map(fn($userInterested) => $userInterested->getEvent()->getId(), $userInterested);

        $allpartcipate = $participationEventRepository->findAll();

        return $this->render('community/show.html.twig', [
            'comm' => $community,
            'events' => $events,
            'communitys' =>$communitys,
            'chatRooms' => $chatRooms,
            'user'=> $user,
            'userCommIds' => $userCommIds,
            'moderatedCommIds' => $moderatedCommIds,
            'form' => $form->createView(),
            'membres' => $membres,
            'ownCommIds' => $ownCommIds,
            'form_event' => $form_event->createView(),
            'form_chat' => $form_chat->createView(),
            'qrCode' => $qrCode,
            'userChats' => $userChatRoomIds,
            'userParticipationId' => $userParticipationIds,
            'userParticipation' => $userParticipation,
            'userInterested' => $userInterested,
            'userInterestedIds' => $userInterestedIds,
            'allpartcipate' => $allpartcipate,
        ]);
    }
    #[Route('/download-qrcode/{id}', name: 'download_qrcode')]
    public function downloadQrCode(Community $community, QrCodeService $qrCodeService): Response
    {
        return $qrCodeService->downloadQrCode($community);
    }
}
