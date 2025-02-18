<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Entity\Gamifications;
use App\Form\GamificationType;
use App\Repository\GamificationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GamificationController extends AbstractController{
    #[Route('/admin/gamification', name: 'gamification.admin.index')]
    public function index(Request $request,GamificationsRepository $gamificationsRepository,EntityManagerInterface $em): Response
    {
        $games = $gamificationsRepository->findAll();
        $game = new Gamifications();
        $form = $this->createForm(GamificationType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('gamification.admin.index');
        }
        return $this->render('gamification/index.html.twig', [
            'games' => $games,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/gamification/{id}/delete', name: 'gamification.delete')]
    public function delete(Request $request, Gamifications $games,EntityManagerInterface $em,):Response
    {
        $em->remove($games);
        $em->flush();
        return $this->redirectToRoute('gamification.admin.index');
    }
    #[Route('/admin/gamification/{id}/edit', name: 'gamification.edit')]
    public function edit(Request $request, Gamifications $games,EntityManagerInterface $em,):Response
    {
        $form = $this->createForm(GamificationType::class, $games);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($games);
            $em->flush();
            return $this->redirectToRoute('gamification.admin.index');
        }
        return $this->render('gamification/edit.html.twig', [
            'games' => $games,
            'form' => $form->createView(),
        ]);
    }
}
