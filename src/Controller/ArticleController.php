<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Articles;
use App\Form\ArticleType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class ArticleController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function base(ArticlesRepository $repository):Response
    {
        $categories = $repository->findUniqueCategories();
        return $this->render('base.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/articles', name: 'app_article')]
    public function index(ArticlesRepository $repository): Response
    {
        $articles = $repository->findAll();
        $categories = $repository->findUniqueCategories();
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    #[Route('/article/{id}', name: 'show_article' , requirements: ['id' => '\d+'])]
    public function show(ArticlesRepository $repository, int $id): Response
    {
        $article = $repository->find($id);
        $categories = $repository->findUniqueCategories();
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'categories' => $categories
        ]);
    }

    #[Route('/article/create', name: 'create_article')]
    public function create(ArticlesRepository $repository, EntityManagerInterface $em, Request $request): Response
    {
        $categories = $repository->findUniqueCategories();
        $article = new Articles();
        
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(), 
            'categories' => $categories
        ]);
    }

    #[Route('/article/{id}/edit', name: 'edit_article')]
    public function edit(ArticlesRepository $repository, EntityManagerInterface $em, int $id, Request $request): Response
    {
        $categories = $repository->findUniqueCategories();
        $article = $repository->find($id);
        
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(), 
            'categories' => $categories
        ]);
    }


    #[Route('/article/{id}/delete', name: 'delete_article', requirements: ['id' => '\d+'])]
    public function delete(ArticlesRepository $repository, int $id, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Articles::class)->find($id);
        $categories = $repository->findUniqueCategories();

        if ($article) {
            $em->remove($article);
            $em->flush();

            // Redirection vers la liste des articles après suppression
            return $this->redirectToRoute('app_article');
        }

        // Affichage d'un message d'erreur si l'article n'est pas trouvé
        return $this->render('article/delete.html.twig', [
            'error' => 'Article non trouvé',
            'categories' => $categories
        ]); 
    }

}
