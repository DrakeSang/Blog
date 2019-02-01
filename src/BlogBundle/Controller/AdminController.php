<?php

namespace BlogBundle\Controller;

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
     * @Route("/", name="all_users")
     */
    public function indexAction()
    {
        /** @var User[] $allUsers */
        $allUsers = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('admin/index.html.twig', array(
            'users' => $allUsers));
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/user/profile/{id}", name="admin_user_profile")
     */
    public function userProfile(User $user)
    {
        return $this->render('admin/user_profile.html.twig', array(
            'user' => $user));
    }
}
