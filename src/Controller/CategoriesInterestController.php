<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class CategoriesInterestController extends AbstractController{
    #[Route('/categories', name: 'categories.index')]
    public function index(Request $request , CategoriesRepository $repository ,EntityManagerInterface $em): Response
    {
        $categories = $repository->findAll();
        return $this->render('categories_interest/index.html.twig' , ['categories' => $categories]);
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

    #[Route('/categories/{id}/edit', name: 'categories.edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Categories $cat ,EntityManagerInterface $em): Response
    {

        $form = $this->createForm(CategoriesType::class, $cat );
        $form = $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success' , 'Categorie modified');
            return $this->redirectToRoute('categories.index');
        }

        return $this->render('categories_interest/edit.html.twig', [
            'cat' => $cat,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/categories/{id}/delete', name: 'categories.del', requirements: ['id' => '\d+'])]
    public function delete(Request $request, Categories $cat ,EntityManagerInterface $em): Response
    {

        $em->remove($cat);
        $em->flush();
        $this->addFlash('success' , 'Categorie deleted');
        return $this->redirectToRoute('categories.index');
    }
    #[Route('/categories/add', name: 'categories.add')]
    public function add(Request $request ,EntityManagerInterface $em): Response
    {
        $cat=new Categories();
        $form = $this->createForm(CategoriesType::class, $cat );
        $form = $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cat->setDateCreation(new \DateTime());
            $em->persist($cat);
            $em->flush();
            $this->addFlash('success' , 'Categorie Added');
            return $this->redirectToRoute('categories.index');
        }

        return $this->render('categories_interest/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
