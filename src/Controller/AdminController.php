<?php

namespace App\Controller;

use App\Entity\Community;
use App\Repository\CommunityRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\VisitorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AdminController extends AbstractController{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, VisitorsRepository $repository,CommunityRepository $communityRepository,MembreComunityRepository $membreComunityRepository): Response
    {
        $session = $request->getSession();

        if (!$this->getUser()) {
            $session->set('_security.target_path', $this->generateUrl('app_admin'));
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        if (in_array('ROLE_USER', $user->getRoles())) {
            return $this->redirectToRoute('access_denied');
        }

        $visitors = $repository->findAll();
        $nbr = $visitors[0]->getNbrVisitors();

        $communitiesFront = $communityRepository->findBy(['statut' => 0]);
        $owners = $membreComunityRepository->findOwnersForNoActiveCommunities();


        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'nbr' => $nbr,
            'user' => $user,
            'communities' => $communitiesFront,
            'owners' => $owners,
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

    #[Route('/admin/waitlist/detail/{id}', name: 'app_admin.waitlist', requirements: ['id'=>'\d+'])]
    public function waitlist(int $id ,Request $request,CommunityRepository $communityRepository): Response
    {
        $user = $this->getUser();
        $community = $communityRepository->find($id);
        return $this->render('admin/waitlist.html.twig',[
            'user'=>$user,
            'community' => $community,
        ]);

    }
    #[Route('/admin/waitlist/accept/{id}', name: 'app_admin.accept', requirements: ['id'=>'\d+'])]
    public function accept(int $id ,Request $request,CommunityRepository $communityRepository,EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');
        $community = $communityRepository->find($id);
        $community->setStatut(1);
        $em->persist($community);
        $em->flush();
        return $this->redirect($referer);

    }
}
