<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Entity\Gamifications;
use App\Form\GamificationType;
use App\Repository\GamificationsRepository;
use App\Repository\InscriptionAbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GamificationController extends AbstractController{
    #[Route('/admin/gamification', name: 'gamification.admin.index')]
    public function index(Request $request,GamificationsRepository $gamificationsRepository,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
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
            "user" => $user,
        ]);
    }

    #[Route('/admin/gamification/{id}/delete', name: 'gamification.delete')]
    public function delete(Request $request, Gamifications $games,EntityManagerInterface $em,):Response
    {
        $user = $this->getUser();
        $em->remove($games);
        $em->flush();
        return $this->redirectToRoute('gamification.admin.index');
    }
    #[Route('/admin/gamification/{id}/edit', name: 'gamification.edit')]
    public function edit(Request $request, Gamifications $games,EntityManagerInterface $em,):Response
    {
        $user = $this->getUser();
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
            'user' => $user,
        ]);
    }
    #[Route('/gamification/{id}', name: 'gamification')]
    public function gamification(Gamifications $gamifications,Request $request,EntityManagerInterface $em,):Response
    {
        $user = $this->getUser();

        return $this->render('abonnement/gamification.html.twig', [
            'user' => $user,
            'gamifications' => $gamifications,
        ]);
    }
    #[Route('/gamification/active/{point}', name: 'gamification.active')]
    public function gamificationActive(int $point, Request $request, EntityManagerInterface $em, InscriptionAbonnementRepository $inscriptionAbonnementRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $user->setPoints($user->getPoints() - $point);

        $inscriptionAbonnement = $inscriptionAbonnementRepository->findBy(['user' => $user]);

        if (!$inscriptionAbonnement) {
            throw $this->createNotFoundException('No subscription found for this user.');
        }

        $inscriptionAbonnement[0]->setExpiredAt((new \DateTimeImmutable())->modify('+1 month'));

        $em->persist($inscriptionAbonnement[0]);
        $em->persist($user);
        $em->flush();

        return $this->redirect($referer);
    }
}
