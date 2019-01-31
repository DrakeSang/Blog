<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @Route("/article/create", name="article_create")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     */
    public function createArticle(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var User $currentUser */
            $currentUser = $this->getUser();

            $article->setAuthor($currentUser);
            $currentUser->addPost($article);

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try{
                $file->move($this->getParameter('article_directory'),
                    $fileName);
            }catch (FileException $exception){

            }

            $article->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/create.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @param $id
     *
     * @Route("/article/{id}", name="article_view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewArticle($id)
    {

        /** @var Article $article */
        $article = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);


        /** @var Comment[] $comments */
        $comments = $this
            ->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['article' => $article], ['dateAdded' => 'desc']);

        $article->setViewCount($article->getViewCount() + 1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return  $this->render("article/details.html.twig",
            ['article' => $article, 'comments' => $comments]);
    }

    /**
     * @Route("/article/edit/{id}", name="article_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editArticle($id, Request $request)
    {

        /** @var Article $article */
        $article = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($article == null) {
            return $this->redirectToRoute("blog_index");
        }

        if(!$currentUser->isAuthor($article) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("blog_index");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {


            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try{
                $file->move($this->getParameter('article_directory'),
                    $fileName);
            }catch (FileException $exception){

            }

            $article->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('article_view',
                array('id' => $article->getId()));
        }

        return $this->render('article/edit.html.twig',
            array('article' => $article,
                'form' => $form->createView()));
    }

    /**
     * @Route("/article/delete/{id}", name="article_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteArticle($id, Request $request)
    {
        /** @var Article $article */
        $article = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($article == null) {
            return $this->redirectToRoute("blog_index");
        }

        if(!$currentUser->isAuthor($article) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("blog_index");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('article/delete.html.twig',
            array('article' => $article,
                'form' => $form->createView()));
    }

    /**
     * @Route("/myArticles", name="myArticles")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function myArticles()
    {
        $userId = $this->getUser()->getId();

        /** @var Article $articles */
        $articles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(['authorId' => $userId]);

        return $this->render("article/myArticles.html.twig",
            array('articles' => $articles));
    }

    /**
     * @Route("/article/like/{id}", name="article_likes")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleLikes($id)
    {
        var_dump($id);
        return $this->redirectToRoute('blog_index');
    }
}
