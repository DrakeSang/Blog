<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
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
        // replace this example code with whatever you need
        $articles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findBy([], ['viewCount' => 'desc']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            2/*limit per page*/
        );

        return $this->render('default/index.html.twig',
            ['pagination' => $pagination]);
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
}
