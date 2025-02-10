<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CategoriesInterestController extends AbstractController{
    #[Route('admin/categories', name: 'categories.index')]
    public function index(Request $request, CategoriesRepository $repository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $categories = $repository->findAll();

        $cat = new Categories();
        $form = $this->createForm(CategoriesType::class, $cat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();

            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed!');
                    return $this->redirectToRoute('categories.index');
                }

                $cat->setCover('/uploads/' . $newFilename);
            }

            $cat->setDateCreation(new \DateTime());
            $em->persist($cat);
            $em->flush();

            $this->addFlash('success', 'Category Added');
            return $this->redirectToRoute('categories.index');
        }

        return $this->render('categories_interest/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{slug}-{id}', name: 'categories.detail' ,  requirements:['id' => '\d+' , 'slug' => '[a-zA-Z0-9-]+'])]
    public function show(Request $request , string $slug , int $id ,CategoriesRepository $repository): Response
    {
        $cat=$repository->find($id);
        if(!$cat->getNom()==$slug){
            return $this->redirectToRoute('categories.detail' , ['slug' =>  $cat->getNom() , 'id'=> $cat->getId()]);
        }
        return $this->render('categories_interest/detail.html.twig' , ['cat' => $cat]);
    }

    #[Route('admin/categories/{id}/edit', name: 'categories.edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Categories $cat ,EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoriesType::class, $cat );
        $form = $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();

            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('cover_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed!');
                    return $this->redirectToRoute('categories.index');
                }

                if ($cat->getCover()) {
                    $oldFilePath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($cat->getCover());
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $cat->setCover('/uploads/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success' , 'Categorie modified');
            return $this->redirectToRoute('categories.index');
        }

        return $this->render('categories_interest/edit.html.twig', [
            'cat' => $cat,
            'form' => $form->createView(),
        ]);
    }
    #[Route('admin/categories/{id}/delete', name: 'categories.del', requirements: ['id' => '\d+'])]
    public function delete(Request $request, Categories $cat, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        if ($cat->getCover()) {
            $coverPath = $this->getParameter('cover_directory') . DIRECTORY_SEPARATOR . basename($cat->getCover());
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }else{
                $logger->error("File not found: " . $coverPath);
            }
        }

        $em->remove($cat);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute('categories.index');
    }


}
