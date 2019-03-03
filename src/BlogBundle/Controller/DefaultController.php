<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Category;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $categoryChoice = 'ALL';
        if(!empty($_GET)){
            $categoryChoice = $_GET['categoryChoice'];
        }

        /** @var Category[] $categories */
        $categories = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        /** @var Article[] $articles */
        $allArticles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getArticlesByCategory($categoryChoice);

        $pagination = new Pagination();
        $pagination->setTotalRecords($allArticles);
        $pagination->setLimit(2);

        $page = $pagination->getCurrentPage();

        /** @var Article[] $articles */
        $articlesPerPage = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getArticlesByPage($categoryChoice, $pagination->getLimit(), $pagination->getOffset($page));

        return $this->render('default/index.html.twig',
            array(
                'categories' => $categories,
                'articlesPerPage' => $articlesPerPage,
                'pages' => $pagination->getTotalPages(),
                'categoryChoice' => $categoryChoice
            ));
    }

    /**
     * @Route("/article", name="find_article_by_name")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function findArticleByName(Request $request)
    {
        $articleName = "";
        if($request->getMethod() == "POST"){
            $articleName = $request->request->get('article_name');

            /** @var Article $article */
            $article = $this
                ->getDoctrine()
                ->getRepository(Article::class)
                ->findOneBy(['title' => $articleName]);

            if($article !== null) {
                /** @var Comment[] $comments */
                $comments = $this
                    ->getDoctrine()
                    ->getRepository(Comment::class)
                    ->findBy(['article' => $article], ['dateAdded' => 'desc']);

                return  $this->render("article/details.html.twig",
                    ['article' => $article, 'comments' => $comments]);
            }

        }

        return $this->render("article/missing.html.twig", [
            'article_name' => $articleName
        ]);
    }

    /**
     * @Route("/allArticles", name="allArticles")
     * @Route("/Games", name="Games")
     * @Route("/Hardware", name="Hardware")
     * @Route("/Phones", name="Phones")
     * @Route("/Programming", name="Programming")
     * @Route("/Software", name="Software")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function articlesByCategory(Request $request)
    {
        $categoryChoice = "";
        if($request->getMethod() == "POST") {
            $categoryChoice = $request->request->get('categoryChoice');
        }

        /** @var Article[] $articles */
        $allArticles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getArticlesByCategory($categoryChoice);

        $pagination = new Pagination();
        $pagination->setTotalRecords($allArticles);
        $pagination->setLimit(2);
        $page = $pagination->getCurrentPage();

        /** @var Article[] $articles */
        $articlesPerPage = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getArticlesByPage($categoryChoice, $pagination->getLimit(), $pagination->getOffset($page));

        return $this->render("article/allArticles.html.twig",
            array(
                'articlesPerPage' => $articlesPerPage,
                'pages' => $pagination->getTotalPages(),
                'categoryChoice' => $categoryChoice
            ));
    }
}
