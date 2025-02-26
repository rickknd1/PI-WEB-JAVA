<?php

namespace App\Controller;

use App\Entity\InscriptionAbonnement;
use App\Form\AbonnementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscriptionAbonnementController extends AbstractController
{
    #[Route('/inscription/abonnement/{id}', name: 'app_inscription_abonnement')]
    public function index(Request $request,EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');
        $user=$this->getUser();
        $inscriptionAbonnement = new InscriptionAbonnement();
        $form = $this->createForm(AbonnementType::class, $inscriptionAbonnement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $inscriptionAbonnement->setUser($user->getId());
            $inscriptionAbonnement->setSubscribedAt(new \DateTimeImmutable());
            $inscriptionAbonnement->setExpiredAt((new \DateTimeImmutable())->modify('+1 month'));
            $em->persist($inscriptionAbonnement);
            $em->flush();
            return $this->redirect($referer);
        }else{
            return $this->redirectToRoute('access_denied');
        }
    }
}
