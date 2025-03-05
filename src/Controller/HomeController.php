<?php

namespace App\Controller;

use App\Entity\Visitors;
use App\Repository\VisitorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController{
    #[Route('/', name: 'home')]
    public function index(Request $request, EntityManagerInterface $em, VisitorsRepository $repository): Response
    {
        // Récupérer les visiteurs existants
        $visitor = $repository->findAll();

        if (empty($visitor)) {
            // Si aucun visiteur en base, créer une nouvelle entrée
            $newVisitor = new Visitors();
            $newVisitor->setNbrVisitors(1);
            $em->persist($newVisitor);
            $em->flush();
        } else {
            // Sinon, incrémenter le nombre de visiteurs
            $visitor[0]->setNbrVisitors($visitor[0]->getNbrVisitors() + 1);
            $em->persist($visitor[0]);
            $em->flush();
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/acceul', name: 'acceul')]
    public function Acceuil(Request $request, EntityManagerInterface $em, VisitorsRepository $repository): Response
    {

        return $this->render('front/feed.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}