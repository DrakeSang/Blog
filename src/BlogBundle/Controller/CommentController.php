<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends Controller
{
    /**
     * @param Request $request
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     *
     * @Route("/article/{id}/comment", name="add_comment")
     */
    public function addComment(Request $request, Article $article)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

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
            ['id' => $article->getId()]);
    }
}
