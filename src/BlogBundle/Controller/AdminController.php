<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package BlogBundle\Controller
 *
 * @Route("admin")
 */
class AdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="admin_index")
     */
    public function indexAction()
    {
        /** @var User[] $allUsers */
        $allUsers = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        /** @var Article[] $allArticles */
        $allArticles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('admin/index.html.twig', array(
            'users' => $allUsers,
            'articles' => $allArticles
        ));
    }
}
