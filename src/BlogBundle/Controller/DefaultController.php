<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Category;
use BlogBundle\Entity\User;
use BlogBundle\Entity\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
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

        /** @var Article[] $allArticles */
        $allArticles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getArticlesByCategory($categoryChoice);

        $pagination = new Pagination();
        $pagination->setTotalRecords($allArticles);
        $pagination->setLimit(2);

        $page = $pagination->getCurrentPage();

        /** @var Article[] $articlesPerPage */
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
}