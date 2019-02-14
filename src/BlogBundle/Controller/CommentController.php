<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CommentController extends Controller
{
    /**
     * @param Request $request
     * @param Article $article
     *
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/article/{id}/comment", name="article_comment")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function addComment(Request $request, Article $article)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            /** @var User $author */
            $author = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->find($user->getId());

            $author->addComment($comment);
            $article->addComment($comment);

            $comment->setAuthor($author);
            $comment->setArticle($article);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('article_view',
                array('id' => $article->getId()));
        }
        return $this->render('article/comment.html.twig',
            array(
                'article' => $article,
                'form' => $form->createView()
            ));
    }
}
