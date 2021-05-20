<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // On importe la librairie Faker pour les fixtures, les articles, commentaire et categories virtuels
        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 categories
        for($i = 1; $i <= 3; $i++)
        {
            $category = new Category;

            $category->setTitre($faker->word)
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 9 articles
            for($j = 1; $j <= mt_rand(4,9); $j++)
            {
                $article = new Article;

                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>';

                $article->setTitre($faker->sentence())
                        ->setContenu($content)
                        ->setImage("https://picsum.photos/id/23$j/300/300")
                        ->setDate($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);

                $manager->persist($article);

                // Création de 4 à 10 commentaires
                for($k = 1; $k <= mt_rand(4,10); $k++)
                {
                    $comment = new Comment;

                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $now = new DateTime;
                    $interval = $now->diff($article->getDate()); // retourne un timestamp (temps en secondes) entre la date de création des articles et aujourd'hui
                    $days = $interval->days; // retourne le nombre de jour entre la date de création des articles et aujourd'hui
                    $minimum = "-$days days"; /* -100 days | le but est d'avoir des dates de commentaires entre la date de création des articles et aujourd'hui, exemple de - 100 jours à aujourd'hui */ 

                    $comment->setAuteur($faker->name)
                            ->setCommentaire($content)
                            ->setDate($faker->dateTimeBetween($minimum)) 
                            ->setArticle($article);

                    $manager->persist($comment);

                }
            }
        }

        $manager->flush();
    }
}
