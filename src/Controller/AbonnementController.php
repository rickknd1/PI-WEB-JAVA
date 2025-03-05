<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Entity\InscriptionAbonnement;
use App\Form\AbonnementType;
use App\Form\IncriptionAbonnementType;
use App\Repository\AbonnementsRepository;
use App\Repository\InscriptionAbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\FlouciService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    #[Route('/FeedBack', name: 'FeedBack')]
    public function FeedBack(): Response
    {   $user = $this->getUser();
        return $this->render('abonnement/payment.html.twig',[
            'user' => $user,
        ]);
    }
    #[Route('/checkout/{id}/{mp}/{ra}', name: 'checkout')]
    public function checkout(Abonnements $abonnements,string $mp,string $ra, string $stripeSK): Response
    {
        $stripe = new \Stripe\StripeClient($stripeSK);

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'EUR',
                    'product_data' => [
                        'name' => $abonnements->getNom(),
                    ],
                    'unit_amount' => $abonnements->getPrix() * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', ['id' => $abonnements->getId(),'mp'=>$mp,'ra'=>$ra], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_abonnement', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkout_session->url, 303);
    }
    #[Route('/payment-success/{id}/{mp}/{ra}', name: 'payment_success')]
    public function paymentSuccess(Abonnements $abonnement,string $mp,string $ra, EntityManagerInterface $em, InscriptionAbonnementRepository $inscriptionAbonnementRepository): Response {
        $user = $this->getUser();

        $existingSubscription = $inscriptionAbonnementRepository->findOneBy([
            'user' => $user,
            'abonnement' => $abonnement,
        ]);

        if ($existingSubscription) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet abonnement.');
            return $this->redirectToRoute('app_abonnement');
        }

        $inscriptionAbonnement = new InscriptionAbonnement();
        $inscriptionAbonnement->setUser($user);
        $inscriptionAbonnement->setAbonnement($abonnement);
        $inscriptionAbonnement->setSubscribedAt(new \DateTimeImmutable());
        $inscriptionAbonnement->setExpiredAt((new \DateTimeImmutable())->modify('+1 month'));
        $inscriptionAbonnement->setModePaiement($mp);
        if ($ra== 'Oui'){
            $inscriptionAbonnement->setRenouvellementAuto(1);
        }else{
            $inscriptionAbonnement->setRenouvellementAuto(0);

        }

        $em->persist($inscriptionAbonnement);
        $em->flush();

        $this->addFlash('success', 'Paiement réussi et abonnement activé !');
        return $this->redirectToRoute('app_abonnement');
    }
    #[Route('/cancel_url', name: 'cancel_url')]
    public function cancelUrl(): Response
    {   $user = $this->getUser();
        return $this->render('abonnement/cancel.html.twig',[
            'user' => $user,
        ]);
    }

}
