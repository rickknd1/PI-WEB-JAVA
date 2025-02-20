<?php

namespace App\Controller;

use App\Entity\Community;
use App\Entity\MembreComunity;
use App\Form\CommunityType;
use App\Repository\CategoriesRepository;
use App\Repository\ChatRoomsRepository;
use App\Repository\CommunityRepository;
use App\Repository\EventsRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\VisitorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CommunityController extends AbstractController{
    #[Route('/community', name: 'community.index.front')]
    #[Route('admin/community', name: 'community.index')]
    public function index(Request $request,CommunityRepository $repository,EntityManagerInterface $em, SluggerInterface $slugger,CategoriesRepository $categoriesRepository,MembreComunityRepository $membreComunityRepository): Response
    {

        $user = $this->getUser();
        $userId = $user->getId();
        $userComm = $membreComunityRepository->findByUserId($userId);
        $userCommIds = array_map(fn($item) => $item->getIdComunity()->getId(), $userComm);
        $routeName = $request->attributes->get('_route');
        $cats = $categoriesRepository->findAll();
        $communitiesFront = $repository->findAll();

        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $communities = $repository->paginateCommunity($page , $limit);
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
            }else{
                $community->setNbrMembre(0);
            }

            $community->setCreatedAt(new \DateTimeImmutable());

            $em->persist($community);
            $em->flush();
            $this->addFlash('success', 'Community created!');

            if ($routeName === 'community.index.front'){
                $membreComunity = new MembreComunity();
                $membreComunity->setIdUser($user);
                $membreComunity->setIdComunity($community);
                $membreComunity->setStatus('moderator');
                $membreComunity->setDateAdhesion(new \DateTime());

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
    public function edit(Request $request, Community $community, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
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
            return $this->redirectToRoute('community.index');
        }
        return $this->render('community/edit.html.twig', [
            'community' => $community,
            'form' => $form->createView(),
            'user'=> $user,
        ]);
    }
    #[Route('admin/community/{id}/delete', name: 'community.del' , requirements: ['id'=>'\d+'])]
    public function delete(Request $request, Community $community, EntityManagerInterface $em, LoggerInterface $logger): Response
    {

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
        return $this->redirectToRoute('community.index');
    }
    #[Route('/community/{id}/events', name: 'community.events' , requirements: ['id'=>'\d+'])]
    #[Route('/community/{id}', name: 'community.detail', requirements: ['id' => '\d+'])]
    public function detail(Community $community,EventsRepository $eventsRepository,CommunityRepository $communityRepository,ChatRoomsRepository $chatRoomsRepository): Response
    {
        $user = $this->getUser();
        $events = $eventsRepository->findBy(['id_community' => $community]);
        $communitys = $communityRepository->findAll();
        $chatRooms = $chatRoomsRepository->findBy(['community' => $community]);
        return $this->render('community/show.html.twig', [
            'comm' => $community,
            'events' => $events,
            'communitys' =>$communitys,
            'chatRooms' => $chatRooms,
            'user'=> $user,
        ]);
    }


    #[Route('/community/{id}/addmembre' , name: 'membre.add' , requirements: ['id'=>'\d+'])]
    public function addmembre(Request $request,EntityManagerInterface $em , CommunityRepository $repository ,int $id)
    {
        $user = $this->getUser();
        $comm=$repository->find($id);
        $comm->setNbrMembre($comm->getNbrMembre()+1);
        $em->persist($comm);
        $em->flush();
        return $this->redirectToRoute('community.index.front');
    }
}
