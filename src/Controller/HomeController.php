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
    public function index(Request $request,EntityManagerInterface $em , VisitorsRepository $repository): Response
    {

        $visitor = $repository->findAll();
        $visitor[0]->setNbrVisitors($visitor[0]->getNbrVisitors() + 1);
        $em->persist($visitor[0]);
        $em->flush();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/home', name: 'app_front')]
    public function home(Request $request,EntityManagerInterface $em , VisitorsRepository $repository): Response
    {

        return $this->render('front/feed.html.twig');
    }
}
