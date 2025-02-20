<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventsType;
use App\Repository\CommunityRepository;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class EventController extends AbstractController{
    #[Route('admin/events', name: 'event.index')]
    public function index(Request $request,EventsRepository $repository,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
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
        $user = $this->getUser();
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
    public function delete(Request $request, Events $events, EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if($events->getCover()){
            $oldCover = $this->getParameter('cover_directory').DIRECTORY_SEPARATOR . basename($events->getCover());
            if (file_exists($oldCover)) {
                unlink($oldCover);
            }
        }
        $em->remove($events);
        $em->flush();
        $this->addFlash('success', 'Event deleted!');
        return $this->redirectToRoute('event.index');
    }

    #[Route('/admin/event/{slug}-{id}', name: 'event.show', requirements: ['id'=>'\d+','slug'=>'[a-zA-Z0-9-]+'])]
    public function detail(Request $request, Events $events,string $slug ,int $id, EntityManagerInterface $em,CommunityRepository $repository): Response
    {
        $user = $this->getUser();
        $event=$repository->find($id);
        if (!$event->getNom()==$slug) {
            return $this->redirectToRoute('event.show',['id'=>$event->getId(),'slug'=>$event->getNom()]);
        }
        return $this->render('event/detail.html.twig', [
            'event'=>$event,
            'user'=>$user,
        ]);
    }
}
