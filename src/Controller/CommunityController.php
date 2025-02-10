<?php

namespace App\Controller;

use App\Entity\Community;
use App\Form\CommunityType;
use App\Repository\CommunityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CommunityController extends AbstractController{
    #[Route('admin/community', name: 'community.index')]
    public function index(Request $request,CommunityRepository $repository,EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $communities = $repository->findAll();
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
            $community->setCreatedAt(new \DateTimeImmutable());
            $em->persist($community);
            $em->flush();
            $this->addFlash('success', 'Community created!');
            return $this->redirectToRoute('community.index');
        }

        return $this->render('community/index.html.twig', [
            'communities' => $communities,
            'form' => $form->createView()
        ]);
    }
    #[Route('admin/community/{id}/edit', name: 'community.edit' , requirements: ['id'=>'\d+'])]
    public function edit(Request $request, Community $community, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
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
            'form' => $form->createView()
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
    #[Route('/community/{slug}-{id}', name: 'community.detail' , requirements: ['id'=>'\d+','slug'=>'[a-zA-Z0-9-]+'])]
        public function detail(Request $request, Community $community,string $slug ,int $id, EntityManagerInterface $em,CommunityRepository $repository): Response
    {
        $comm=$repository->find($id);
        if (!$comm->getNom()==$slug) {
            return $this->redirectToRoute('community.detail', ['id'=>$comm->getId(),'slug'=>$comm->getNom()]);
        }
        return $this->render('community/detail.html.twig', ['comm'=>$comm]);
    }
}
