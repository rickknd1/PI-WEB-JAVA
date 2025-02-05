<?php

namespace App\Controller;

use App\Entity\Community;
use App\Form\CommunityType;
use App\Repository\CommunityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommunityController extends AbstractController{
    #[Route('admin/community', name: 'community.index')]
    public function index(Request $request,CommunityRepository $repository,EntityManagerInterface $em): Response
    {
        $communities = $repository->findAll();
        $community = new Community();
        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
    public function edit(Request $request, Community $community, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
    public function delete(Request $request, Community $community, EntityManagerInterface $em): Response
    {
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
            return $this->redirectToRoute('community.detail', ['id'=>$comm->getId(),'slug'=>$comm->getSlug()]);
        }
        return $this->render('community/detail.html.twig', ['comm'=>$comm]);
    }
}
