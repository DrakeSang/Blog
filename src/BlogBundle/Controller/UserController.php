<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Role;
use BlogBundle\Entity\User;
use BlogBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());

            $role = $this
                ->getDoctrine()
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($role);
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("security_login");
        }

        return $this->render('user/register.html.twig');
    }

    /**
     * @Route("/profile", name="user_profile")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function profile()
    {
        $id = $this->getUser()->getId();

        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        return $this->render("user/profile.html.twig",
            ['user' => $user]);
    }
}
