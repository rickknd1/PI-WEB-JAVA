<?php

namespace App\Controller;

use App\Entity\Community;
use App\Entity\MembreComunity;
use App\Repository\CommunityRepository;
use App\Repository\MembreComunityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MembreComunityController extends AbstractController{
    #[Route('/membre_comunity/add/{id}', name: 'membre.comunity.add')]
    public function index(Community $community,EntityManagerInterface $em,Request $request,CommunityRepository $communityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $membreComunity = new MembreComunity();
        $membreComunity->setIdUser($user);
        $membreComunity->setIdComunity($community);
        $membreComunity->setStatus('membre');
        $membreComunity->setDateAdhesion(new \DateTime());

        $community->setNbrMembre($community->getNbrMembre()+1);
        $em->persist($community);
        $em->persist($membreComunity);
        $em->flush();

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('community.index.front', [
            'user' => $user->getId(),
        ]);
    }
    #[Route('/membre_comunity/remove/{id}', name: 'membre.comunity.remove')]
    public function remove(Community $community, EntityManagerInterface $em, Request $request, MembreComunityRepository $membreComunityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $membreComunity = $membreComunityRepository->findOneBy([
            'id_user' => $user,
            'id_comunity' => $community
        ]);

        if ($membreComunity) {
            $em->remove($membreComunity);

            $community->setNbrMembre(max(0, $community->getNbrMembre() - 1));
            $em->persist($community);

            $em->flush();
        }

        return $this->redirect($referer ?: $this->redirectToRoute('community.index.front', [
            'user' => $user->getId(),
        ]));
    }

}
