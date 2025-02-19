<?php

namespace App\Controller;

use App\Repository\VisitorsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AdminController extends AbstractController{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, VisitorsRepository $repository): Response
    {
        $session = $request->getSession();

        if (!$this->getUser()) {
            $session->set('_security.target_path', $this->generateUrl('app_admin'));
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }

        $visitors = $repository->findAll();
        $nbr = $visitors[0]->getNbrVisitors();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'nbr' => $nbr,
            'user' => $user,
        ]);
    }

    #[Route('/access-denied', name: 'access_denied')]
    public function accessDenied(): Response
    {
        $user = $this->getUser();
        return $this->render('security/access_denied.html.twig',[
            'user'=>$user
        ]);
    }
}
