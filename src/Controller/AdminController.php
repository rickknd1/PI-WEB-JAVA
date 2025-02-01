<?php

namespace App\Controller;

use App\Repository\VisitorsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request,VisitorsRepository $repository): Response
    {
        $visitors = $repository->findAll();
        $nbr=$visitors[0]->getNbrVisitors();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'nbr'=>$nbr
        ]);
    }
}
