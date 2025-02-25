<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Entity\InscriptionAbonnement;
use App\Form\AbonnementType;
use App\Form\IncriptionAbonnementType;
use App\Repository\AbonnementsRepository;
use App\Repository\InscriptionAbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AbonnementController extends AbstractController
{
    #[Route('/abonnement', name: 'app_abonnement')]
    public function index(Request $request,EntityManagerInterface $em,AbonnementsRepository $abonnementsRepository,InscriptionAbonnementRepository $inscriptionAbonnementRepository): Response
    {
        $referer = $request->headers->get('referer');
        $abonnements = $abonnementsRepository->findAll();
        $user = $this->getUser();

        $inscriptionAbonnement = new InscriptionAbonnement();
        $form = $this->createForm(IncriptionAbonnementType::class, $inscriptionAbonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abonnementId = $form->get('abonnement_id')->getData();
            dump($abonnementId); // Debug here

            if (!$abonnementId) {
                $this->addFlash('error', 'No abonnement selected!');
                return $this->render('abonnement/index.html.twig', [
                    'abonnements' => $abonnements,
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            $abonnement = $abonnementsRepository->find($abonnementId);
            if (!$abonnement) {
                $this->addFlash('error', 'Selected abonnement not found!');
                return $this->render('abonnement/index.html.twig', [
                    'abonnements' => $abonnements,
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            $inscriptionAbonnement->setAbonnement($abonnement);
            $inscriptionAbonnement->setUser($user);
            $inscriptionAbonnement->setSubscribedAt(new \DateTimeImmutable());
            $inscriptionAbonnement->setExpiredAt((new \DateTimeImmutable())->modify('+1 month'));

            $em->persist($inscriptionAbonnement);
            $em->flush();

            return $this->redirect($referer);
        }
        $userabb = $inscriptionAbonnementRepository->findOneBy(['user' => $user]);
        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnements,
            'user' => $user,
            'form' => $form->createView(),
            'userabb' => $userabb,
        ]);
    }

    #[Route('/admin/abonnement', name: 'abonnement.admin.index')]
    public function add(Request $request , AbonnementsRepository $repository , EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
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
            'user' => $user,
        ]);
    }

    #[Route('/admin/abonnement/{id}/delete', name: 'abonnement.delete')]
    public function delete(Request $request, Abonnements $abonnement, EntityManagerInterface $em): Response
    {
        $em->remove($abonnement);
        $em->flush();
        return $this->redirectToRoute('abonnement.admin.index');
    }

    #[Route('/admin/abonnement/{id}/edit', name: 'abonnement.edit')]
    public function edit(Request $request, Abonnements $abonnement, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($abonnement);
            $em->flush();
            return $this->redirectToRoute('abonnement.admin.index');
        }
        return $this->render('abonnement/edit.html.twig', [
            'abonnements' => $abonnement,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}
