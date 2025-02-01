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
            $this->addFlash('succes' , 'cat modified');
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
        return $this->redirectToRoute('categories.index');
    }


    /**
    #[Route('/categories/add', name: 'categories.add')]
    public function addCat(Request $request , CategoriesRepository $repository ,EntityManagerInterface $em): Response
    {
        $categories = $repository->findAll();

        $categorie = new Categories();
        $categorie->setNom('Dance')
            ->setCover('https://www.clistudios.com/wp-content/uploads/2021/08/jaquel-knight-hip-hop-1024x683.jpeg')
            ->setDescription('La danse est un art du mouvement qui exprime des émotions, des idées ou des histoires à travers des gestes et des enchaînements corporels, souvent synchronisés avec de la musique. Elle peut être pratiquée comme une discipline artistique, un loisir ou un rituel culturel. Il existe de nombreux styles de danse, tels que la danse classique (ballet), la danse contemporaine, le hip-hop, la salsa, le tango ou encore les danses traditionnelles. La danse allie créativité, expression personnelle et technique, tout en étant une forme de communication universelle.')
            ->setDateCreation(new \DateTime());
        $em->persist($categorie);
        $em->flush();
        return $this->redirectToRoute('categories.index');
    }
    #[Route('/categories/del', name: 'categories.del')]
    public function delCat(Request $request , CategoriesRepository $repository ,EntityManagerInterface $em): Response
    {
        $categories = $repository->findAll();
        $em->remove($categories[2]);
        $em->flush();
        return $this->redirectToRoute('categories.index');
    }
     */
}
