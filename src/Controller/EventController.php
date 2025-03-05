<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\ParticipationEvent;
use App\Form\EventsType;
use App\Repository\CommunityRepository;
use App\Repository\EventsRepository;
use App\Repository\InscriptionAbonnementRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\ParticipationEventRepository;
use App\Service\WeatherApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\Clock\now;

final class EventController extends AbstractController{
    #[Route('admin/events', name: 'event.index')]
    public function index(Request $request,EventsRepository $repository,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if ($routeName=== "event.index" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $events = $repository->paginateEvents($page , $limit);
        $maxPage = ceil($events->count() / $limit);

        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
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
                    return $this->redirectToRoute('event.index');
                }
                $event->setCover('/uploads/' . $newFilename);
            }
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Event created!');
            return $this->redirectToRoute('event.index');
        }
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
            'maxPage' => $maxPage,
            'page' => $page,
            'limit' => $limit,
            'user' => $user,
        ]);
    }

    #[Route('/admin/event/{id}/edit', name: 'event.edit', requirements: ['id'=>'\d+'])]
    public function edit(Events $events,Request $request, EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if ($routeName=== "event.edit" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
        $form = $this->createForm(EventsType::class, $events);
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
                }catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('event.index');
                }

                if ($events->getCover()) {
                    $oldCover = $this->getParameter('cover_directory'). DIRECTORY_SEPARATOR . basename($events->getCover());
                    if (file_exists($oldCover)) {
                        unlink($oldCover);
                    }
                }
                $events->setCover('/uploads/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Event edited!');
            return $this->redirectToRoute('event.index');
        }
        return $this->render('event/edit.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
            'user' => $user,

        ]);
    }

    #[Route('/admin/event/{id}/delete', name: 'event.del', requirements: ['id'=>'\d+'])]
    #[Route('/event/{id}/delete', name: 'event.front.del', requirements: ['id'=>'\d+'])]
    #[Route('/event/{id}/delete', name: 'event.front.del2', requirements: ['id'=>'\d+'])]
    public function delete(Request $request, Events $events, EntityManagerInterface $em,SluggerInterface $slugger,MembreComunityRepository $membreComunityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
        if ($routeName=== "event.del" && !in_array('ROLE_ADMIN', $user->getRoles())) {
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
        if (in_array($events->getIdCommunity()->getId(), $moderatedCommIds) || in_array($events->getIdCommunity()->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if($events->getCover()){
                $oldCover = $this->getParameter('cover_directory').DIRECTORY_SEPARATOR . basename($events->getCover());
                if (file_exists($oldCover)) {
                    unlink($oldCover);
                }
            }
            $em->remove($events);
            $em->flush();
            $this->addFlash('success', 'Event deleted!');
            if ($routeName === 'event.front.del'){
                return $this->redirect($referer);
            }elseif($routeName === 'event.front.del2'){
                return $this->redirectToRoute('event.index');
            }
            else{
                return $this->redirectToRoute('community.events', ['id' => $event->getIdCommunity()->getId()]);
            }
        }else{
            return $this->redirectToRoute('access_denied');
        }

    }

    #[Route('/admin/event/{id}', name: 'event.show', requirements: ['id'=>'\d+'])]
    #[Route('/event/{id}', name: 'event.front.show', requirements: ['id'=>'\d+'])]
    public function detail(Request $request,int $id, EntityManagerInterface $em,EventsRepository $repository,WeatherApiService $weatherApiService,
                            ParticipationEventRepository $participationEventRepository,MembreComunityRepository $membreComunityRepository
    ): Response
    {
        $routeName = $request->attributes->get('_route');
        $user = $this->getUser();
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
        if ($routeName=== "event.show" && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }
        $event=$repository->find($id);
        $city='Tunis';
        $weather = $weatherApiService->getFutureWeather($city,$event->getStartedAt()->format('Y-m-d'));

        $userParticipation = $participationEventRepository->findBy(['user' => $user, 'type' => 'Participate']);
        $userParticipationIds = array_map(fn($userParticipation) => $userParticipation->getEvent()->getId(), $userParticipation);

        $userInterested = $participationEventRepository->findBy(['user' => $user, 'type' => 'Interested']);
        $userInterestedIds = array_map(fn($userInterested) => $userInterested->getEvent()->getId(), $userInterested);

        $allpartcipate = $participationEventRepository->findAll();

        return $this->render('event/detail.html.twig', [
            'event'=>$event,
            'user'=>$user,
            'weather'=>$weather,
            'userParticipationId' => $userParticipationIds,
            'userParticipation' => $userParticipation,
            'userInterested' => $userInterested,
            'userInterestedIds' => $userInterestedIds,
            'allpartcipate' => $allpartcipate,
            'moderatedCommIds' => $moderatedCommIds,
            'ownCommIds' => $ownCommIds,
        ]);
    }
    #[Route('/meet', name: 'meet', requirements: ['id'=>'\d+'])]
    public function meet(Request $request,int $id, EntityManagerInterface $em,EventsRepository $repository,WeatherApiService $weatherApiService): Response
    {
        $data=json_decode($request->getContent(),true);
        return $this->json($data);
    }

    #[Route('/join/event/{id}', name: 'join.event', requirements: ['id' => '\d+'])]
    public function joinEvent(Events $event, EntityManagerInterface $em, Request $request,InscriptionAbonnementRepository $inscriptionAbonnementRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $existingParticipation = $em->getRepository(ParticipationEvent::class)
            ->findOneBy(['event' => $event, 'user' => $user]);

        if ($existingParticipation) {
            if ($existingParticipation->getType() === 'Interested') {
                $existingParticipation->setType('Participate');
            } else {
                $em->remove($existingParticipation);
                $user->decrementPoints(10);
            }
        } else {
            $eventParticipation = new ParticipationEvent();
            $eventParticipation->setEvent($event);
            $eventParticipation->setUser($user);
            $eventParticipation->setType('Participate');
            $user->incrementPoints(10);
            $userabb = $inscriptionAbonnementRepository->findOneBy(['user' => $user]);
            $em->persist($eventParticipation);
        }

        $em->flush();

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('access_denied');
    }

    #[Route('/interested/event/{id}', name: 'interested.event', requirements: ['id' => '\d+'])]
    public function interestedEvent(Events $event, EntityManagerInterface $em, Request $request): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $existingParticipation = $em->getRepository(ParticipationEvent::class)
            ->findOneBy(['event' => $event, 'user' => $user]);

        if ($existingParticipation) {
            if ($existingParticipation->getType() === 'Participate') {
                $existingParticipation->setType('Interested');
            } else {
                $em->remove($existingParticipation);
            }
        } else {
            $eventParticipation = new ParticipationEvent();
            $eventParticipation->setEvent($event);
            $eventParticipation->setUser($user);
            $eventParticipation->setType('Interested');
            $em->persist($eventParticipation);
        }

        $em->flush();

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('access_denied');
    }

    #[Route('/api/events', name: 'api.events')]
    public function getEvents(EventsRepository $eventRepository): JsonResponse
    {
        $user = $this->getUser();
        $events = $eventRepository->findEventsByUserCommunities($user->getId());
        $eventData = [];

        foreach ($events as $event) {
            if ($event->getStartedAt()->format('Y-m-d H:i:s') > now()->format('Y-m-d H:i:s')) {
                $eventData[] = [
                    'cover' => $event->getCover(),
                    'startedAt' => $event->getStartedAt()->format('Y-m-d H:i:s'),
                    'nom' => $event->getNom(),
                ];
            }
        }

        return new JsonResponse(['events' => $eventData]);
    }

}
