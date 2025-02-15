<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Form\AbonnementType;
use App\Repository\AbonnementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AbonnementController extends AbstractController
{
    #[Route('/abonnement', name: 'app_abonnement')]
    public function index(Request $request,AbonnementsRepository $repository): Response
    {
        $abonnements = $repository->findAll();
        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnements,
        ]);
    }

    #[Route('/admin/abonnement', name: 'abonnement.admin.index')]
    public function add(Request $request , AbonnementsRepository $repository , EntityManagerInterface $em): Response
    {
        $abonnements= $repository->findAll();
        $abonnement = new Abonnements();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($abonnement);
            $em->flush();
            return $this->redirectToRoute('abonnement.admin.index');
        }
        return $this->render('abonnement/add.html.twig', [
            'abonnements' => $abonnements,
            'form' => $form->createView(),
        ]);
    }

}
