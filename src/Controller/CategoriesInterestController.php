<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoriesInterestController extends AbstractController{
    #[Route('/categories', name: 'categories.index')]
    public function index(Request $request , CategoriesRepository $repository): Response
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
}
