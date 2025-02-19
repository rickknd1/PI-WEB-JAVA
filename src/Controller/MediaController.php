<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; 

#[Route('/media')]
final class MediaController extends AbstractController{

    #[Route(name: 'app_media_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        $user = $this->getUser();
        return $this->render('media/index.html.twig', [
            'media' => $mediaRepository->findAll(),
            'user' => $user
        ]);
    }

    #[Route('/new', name: 'app_media_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $medium = new Media();
    $form = $this->createForm(MediaType::class, $medium);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle file upload for the `link` field
        $file = $form->get('link')->getData();
        if ($file) {
            $type = $form->get('type')->getData(); // Get the media type
            $extension = $file->guessExtension();
            $validExtensions = $type === 'image'
                ? ['jpeg', 'png', 'gif', 'webp']
                : ['mp4', 'mpeg', 'avi', 'mov'];

            if (!in_array($extension, $validExtensions)) {
                $this->addFlash('error', 'Le fichier ne correspond pas au type sélectionné.');
                return $this->redirectToRoute('app_media_new');
            }

            $newFilename = uniqid() . '.' . $extension;
            $file->move(
                $this->getParameter('uploads_directory'), // Directory defined in `services.yaml`
                $newFilename
            );

            $medium->setLink('/uploads/media/' .$newFilename);
        }

        $entityManager->persist($medium);
        $entityManager->flush();

        return $this->redirectToRoute('app_lieu_culturel_detail', ['id' => $medium->getLieux()->getId()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('media/new.html.twig', [
        'medium' => $medium,
        'form' => $form,
        'user' => $user
    ]);
}



    #[Route('/{id}', name: 'app_media_show', methods: ['GET'])]
    public function show(Media $medium): Response
    {
        $user = $this->getUser();
        return $this->render('media/show.html.twig', [
            'medium' => $medium,
            'user' => $user
        ]);
    }

    #[Route('/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu_culturel_detail', ['id' => $medium->getLieux()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/{id}', name: 'app_media_delete', methods: ['POST'])]
    public function delete(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($medium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lieu_culturel_detail', ['id' => $medium->getLieux()->getId()], Response::HTTP_SEE_OTHER);
    }
}
