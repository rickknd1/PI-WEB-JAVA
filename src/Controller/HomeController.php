<?php

namespace App\Controller;

use App\Entity\Visitors;
use App\Repository\UserRepository;
use App\Repository\VisitorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;

final class HomeController extends AbstractController{
    #[Route('/welcome', name: 'welcome')]
    public function index(Request $request,EntityManagerInterface $em , VisitorsRepository $repository): Response
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

    #[Route('/', name: 'home')]
    public function indexhome(Request $request, EntityManagerInterface $em, VisitorsRepository $repository,UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        $users = $userRepository->findAll();
        if (!$this->getUser()) {
            $session->set('_security.target_path', $this->generateUrl('home'));
            return $this->redirectToRoute('app_login');
        }

        $visitor = $repository->findAll();
        $visitor[0]->setNbrVisitors($visitor[0]->getNbrVisitors() + 1);
        $em->persist($visitor[0]);
        $em->flush();

        return $this->render('home/home.html.twig', [
            'user' => $this->getUser(),
            'users' => $users,
        ]);
    }

}