<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Category;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use BlogBundle\Entity\Pagination;

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
        /** @var Category[] $categories */
        $categories = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

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
            array('form' => $form->createView(), 'categories' => $categories));
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
        /** @var Category[] $categories */
        $categories = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

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

        $oldFileName = $article->getImage();
        $oldFileNamePath = $this->getParameter('article_directory') . "/{$oldFileName}";

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($article->getImage() != null) {
                /** @var UploadedFile $file */
                $file = $form->getData()->getImage();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                if(file_exists($oldFileNamePath)) {
                    unlink($oldFileNamePath);
                }

                try{
                    $file->move($this->getParameter('article_directory'),
                        $fileName);
                }catch (FileException $exception){

                }

                $article->setImage($fileName);
            } else {
                $article->setImage($oldFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('article_view',
                array('id' => $article->getId()));
        }

        return $this->render('article/edit.html.twig',
            array(
                'article' => $article,
                'form' => $form->createView(),
                'categories' => $categories
            ));
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

        $articleFileName = $article->getImage();
        $articleFilePath = $this->getParameter('article_directory') . "/{$articleFileName}";

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
            if(file_exists($articleFilePath)) {
                unlink($articleFilePath);
            }

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

    /**
     * @param $articleId
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/favourite/article/{articleId}", name="add_to_favourites")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")     *
     */
    public function addToFavourites($articleId, Request $request)
    {
        $arr = array();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($currentUser == null) {
            $arr["message"] = "notLoggedIn";

            return $this->render('security/login.html.twig');
        }else {
            $userId = $currentUser->getId();

            $article = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->getFavouriteArticle($userId, $articleId);

            if(count($article) == 1){
                $arr["message"] = "alreadyAdded";
            }else {
                /** @var Article $article */
                $article = $this
                    ->getDoctrine()
                    ->getRepository(Article::class)
                    ->find($articleId);

                $currentUser->setFavouriteArticle($article);

                $em = $this->getDoctrine()->getManager();
                $em->persist($currentUser);
                $em->flush();

                $arr["message"] = "successful";
            }

            return new JsonResponse($arr);
        }
    }
}
