<?php

namespace App\Controller;

use App\Entity\Community;
use App\Entity\MembreComunity;
use App\Entity\User;
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
        $membreComunity->setIdCommunity($community);
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
            'community' => $community
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
    #[Route('/membre_comunity/remove/{id}/{membre}', name: 'membre.remove')]
    public function removemembre(Community $community,int $membre, EntityManagerInterface $em, Request $request, MembreComunityRepository $membreComunityRepository): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $membreComunity = $membreComunityRepository->findOneBy([
            'id_user' => $membre,
            'community' => $community
        ]);

        $userComm = $membreComunityRepository->findByUserId($user->getId());
        $moderatedCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'moderator')
        );
        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($community->getId(), $moderatedCommIds) || in_array($community->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($membreComunity) {
                $em->remove($membreComunity);

                $community->setNbrMembre(max(0, $community->getNbrMembre() - 1));
                $em->persist($community);

                $em->flush();
            }
        }else{
            return $this->redirectToRoute('access_denied');
        }

        return $this->redirect($referer ?: $this->redirectToRoute('community.index.front', [
            'user' => $user,
        ]));
    }

    #[Route('/membre_comunity/promote/{id}/{membre}', name: 'membre.promote')]
    public function promote(Community $community,int $membre,MembreComunityRepository $membreComunityRepository,Request $request,EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        $membreComunity = $membreComunityRepository->findOneBy([
            'id_user' => $membre,
            'community' => $community
        ]);

        $userComm = $membreComunityRepository->findByUserId($user->getId());

        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($community->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if (!$membreComunity) {
                $this->addFlash('error', 'Member not found.');
                return $this->redirectToRoute('community.index.front', ['user' => $user]);
            }

            $membreComunity->setStatus('moderator');
            $em->persist($membreComunity);
            $em->flush();
        }else{
            return $this->redirectToRoute('access_denied');
        }

        return $this->redirect($referer ?: $this->generateUrl('community.index.front', [
            'user' => $user
        ]));
    }
    #[Route('/membre_comunity/demote/{id}/{membre}', name: 'membre.demote')]
    public function demote(Community $community,int $membre ,MembreComunityRepository $membreComunityRepository,Request $request,EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        $membreComunity = $membreComunityRepository->findOneBy([
            'id_user' => $membre,
            'community' => $community
        ]);
        $userComm = $membreComunityRepository->findByUserId($user->getId());

        $ownCommIds = array_map(
            fn($item) => $item->getCommunity()->getId(),
            array_filter($userComm, fn($item) => $item->getStatus() === 'owner')
        );
        if (in_array($community->getId(), $ownCommIds) || in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($membreComunity) {
                $membreComunity->setStatus('membre');
                $em->persist($membreComunity);
                $em->flush();
            }
        }else{
            return $this->redirectToRoute('access_denied');
        }
        return $this->redirect($referer ?: $this->redirectToRoute('community.index.front', [
            'user' => $user,
        ]));

    }

}
