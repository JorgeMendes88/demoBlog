<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogController extends AbstractController
{

    /**
     * Méthode permettant de définir la page d'accueil du blog, le piont d'entré du site
     * 
     * @Route("/", name="home")
     */
    public function home(): Response 
    {
        return $this->render('blog/home.html.twig', [
           'title' => 'Bienvenue sur le blog Symfony',
           'age' => 25 
        ]);
    }

    /**
     * Méthode permettant d'afficher tout les articles du blog
     * 
     * @Route("/blog", name="blog")
     */
    public function blog(ArticleRepository $repoArticle): Response
    {
        // Pour selectionner dans une table SQL, nous devons importer une classe Repository 
        // Une classe Repository permet uniquement de selectionner des données dans une table SQL 
        // $repoArticle est un objet issu de la classe ArticleRepository
        // $repoArticle = $this->getDoctrine()->getRepository(Article::class);
        dump($repoArticle); // dd() dump die

        // findAll() est une méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble d'une table SQL
        $articles = $repoArticle->findAll(); // SELECT * FROM article + FETCH_ALL
        dump($articles);

        return $this->render('blog/blog.html.twig', [
            'articles' => $articles // on transmet au template les articles selectionnés en BDD afin de pouvoir les traiter et les afficher en Twig
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function create(Request $request, EntityManagerInterface $manager, Article $article = null, SluggerInterface $slugger): Response 
    {
        // La classe Request permet de stocker les données http véhiculé par les superglobales ($_POST, $_GET, $_FILES etc...)
        // dump($request);

        // Si la variable $article N'EST PAS (null), si elle ne contient aucun article de la BDD, cela veut dire nous avons envoyé la route '/blog/new', c'est une insertion, on entre dans le IF et on crée une nouvelle instance de l'entité Article, création d'un nouvel article
        // Si la variable $article contient un article de la BDD, cela veut dire que nous avons envoyé la route '/blog/id/edit', c'est une modifiction d'article, on entre pas dans le IF, $article ne sera pas null, il contient un article de la BDD, l'article à modifier
        if(!$article)
        {
            $article = new Article;
        }

        // On met en form le formulaire via la méthode createForm à partir de la classe crée ArticleType
        // En 2ème argument, on définit que le formulaire a pour but de remplir l'entité $articles
        $formArticle = $this->createForm(ArticleType::class, $article);

        // Cette méthode permet de récupérer chaque donnée saisie dans le formulaire afin de les envoyer dans les bons setters et propriétés de l'entité $article
        $formArticle->handleRequest($request);

        dump($article);

        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            // Si l'id de l'article n'est pas, est null, alors on entre dans le IF et on génére une date de création de l'article
            if(!$article->getId())
            {
                $article->setDate(new \DateTime());
            }

            // TRAITEMENT DE L'IMAGE
            $image = $formArticle->get('image')->getData();
            dump($image);

            if($image)
            {
                // On récupère le nom d'origine du fichier 
                $nomOrigineFichier = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // dump($nomOrigineFichier);

                // cela est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL
                $secureNomFichier = $slugger->slug($nomOrigineFichier);
                // dump($secureNomFichier);

                //                     jimi-hendrix       -   215184845884 .    jpg
                $nouveauNomFichier = $secureNomFichier . '-' . uniqid() . '.' . $image->guessExtension();
                dump($nouveauNomFichier);

                try{
                    // On copie l'image dans le bon dossier (images_directory service.yaml) 
                    $image->move(
                        $this->getParameter('images_directory'),
                        $nouveauNomFichier
                    );

                }catch(FileException $e){

                }

                // On enregistre l'image en BDD
                $article->setImage($nouveauNomFichier);
            }

            $manager->persist($article); // prépare et garde en mémoire la requete SQL d'insertion
            $manager->flush(); // execute la requete SQL d'insertion

            //Parès l'insertion en BDD de l'article, on redirige l'internaute vers l'affiche de cet article
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId() // blog_show est une route paramétrée, il faut lui fournir l'id a transmettre dans l'URL
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $formArticle->createView(), // createView() crée une, un petit objet permettant de mettre en forme et d'afficher le formulaire dans le template
            'editMode' => $article->getId(), // Si editMode dans le template renvoi TRUE, alors l'article possède un ID, c'est une modification sinon elle renvoi FALSE, c'est une insertion
            'imageArticle' => $article->getImage() // on transmet l'image de l'article afin de l'afficher en cas de modification
        ]);
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * {id} : permet de définir une route paramétré qui va receptionner un id d'1 article de la BDD
     * $id va receptionner l'id transmit dans la route 
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function blogShow(Article $article, Request $request, EntityManagerInterface $manager): Response 
    {
        // dump($id); // 5

        // $repoArticle = $this->getDoctrine()->getRepository(Article::class);
        // $repoArticle : objet issu de la classe ArticleRepository

        // find() : méthode issue de la classe ArticleRepository permettant de selectionner un élément dans la BDD par son id
        // $article = $repoArticle->find($id); // 5
        dump($article);

        // Traitement d'ajout de commentaire 
        $comment = new Comment;

        // On crée le formulaire à partie de la classe CommentType, et on relie le formulaire à l'entité $comment
        $formComment = $this->createForm(CommentType::class, $comment);

        $formComment->handleRequest($request);

        dump($comment);

        // On entre dans la condition If suelement dans le cas où l'on a validé le formulaire et que toute les données on bien été transmise à l'entité $comment 
        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $comment->setDate(new \DateTime) // on insérer une date de création de commentaire 
                    ->setArticle($article); // on relie le commentaire à l'article (clé étrangère)

             // $bdd->prepare("INSERT INTO comment VALUES (2, '$comment->getAuteur()', '$comment->getCommentaire()')")
            $manager->persist($comment); // on prépare et on garde en mémoire la requete d'insertion 
            $manager->flush(); // on execute 

            // Aprsè l'insertion, on affiche un message de validation stocké en session 
            $this->addFlash('success', "Le commentaire a été posté avec succès !");

            // On redirige l'internaute vers l'affichage de l'article juste après l'insertion du commentaire
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/blog_show.html.twig', [
            'article' => $article, // on transmet l'article selectionné en BDD au template pour pouvoir le traiter et l'afficher avec Twig
            'formComment' => $formComment->createView()
        ]);
    }
}
