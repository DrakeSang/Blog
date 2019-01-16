<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @Route("/article/create", name="article_create")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createArticle()
    {
        return $this->render('article/create.html.twig');
    }
}
