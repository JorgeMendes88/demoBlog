<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * Méthode permettant d'afficher l'ensemeble des articles (liens modification, suppression, ajout etc...)
     * 
     * @Route("/admin/articles", name="admin_articles")
     * @Route("/admin/article/{id}/remove", name="admin_article_remove")
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $article = null): Response 
    {
        dump($article);

        // via le manager qui permet de manipuler la BDD (insert, upadte, delete etc...), on execute la méthode getClassMetadata() afin de selectionner les méta données (primary key ,not null, noms des champs etc..) d'une entité (donc d'une table SQL), pour selectionner le nom des champs/colonnes de la table grace à la méthode getFieldNames()
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();
        dump($colonnes);

        $articles = $repoArticle->findAll(); // SELECT * FROM article + FETCH_ALL
        dump($articles);

        // SI la condition IF retourne TRUE, cela veut dire $article contient les informations de l'article a supprimer en BDD, on entre dans le IF

        if($article)
        {
            // Avant de supprimer l'article en BDD, on stock son ID dans une variable afin de l'injecter dans le message de validation

            $id = $article->getId();

            // remove() : méthode Repository --> DELETE FROM article WHERE id = ...

            $manager->remove($article);
            $manager->flush();

            $this->addFlash('success', "L'article n° $id vient d'être supprimé");

            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_articles.html.twig', [
            'colonnes' => $colonnes,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/categories", name="admin_categories")
     */
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repoCategory): Response
    {
        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();
        dump($colonnes);

        $categories = $repoCategory->findAll();
        dump($categories);

        return $this->render('admin/admin_categories.html.twig', [
            'colonnes' => $colonnes,
            'categories' => $categories
        ]);
    }
}
