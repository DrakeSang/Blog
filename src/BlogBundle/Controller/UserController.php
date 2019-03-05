<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Message;
use BlogBundle\Entity\Role;
use BlogBundle\Entity\Article;
use BlogBundle\Entity\User;
use BlogBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        if($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();

            $userDB = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if(null !== $userDB) {
                $this->addFlash('info', "Username with email " . $email . " already taken!");

                return $this->render('user/register.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());

            /** @var Role $role */
            $role = $this
                    ->getDoctrine()
                    ->getRepository(Role::class)
                    ->findOneBy(['name' => 'ROLE_USER']);

            $user->setPassword($password);
            $user->addRole($role);

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try{
                $file->move($this->getParameter('user_directory'),
                    $fileName);
            }catch (FileException $exception){

            }

            $user->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("security_login");
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser($id, Request $request)
    {
        /** @var User $user */
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $profilePic = $user->getImage();
        $profilePicPath = $this->getParameter('user_directory') . "/{$profilePic}";

        $articlePics = array();
        /** @var Article $article */
        foreach ($user->getArticles() as $article)   {
            $articlePics[] = $article->getImage();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if(file_exists($profilePicPath)) {
                unlink($profilePicPath);
            }

            foreach ($articlePics as $articlePic) {
                $articlePicPath = $this->getParameter('article_directory') . "/{$articlePic}";

                if(file_exists($articlePicPath)) {
                    unlink($articlePicPath);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('user/delete.html.twig',
            array('user' => $user,
                'form' => $form->createView()));

    }

    /**
     * @param $id
     *
     * @Route("/user/profile/{id}", name="user_profile")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile($id)
    {
        /** @var User $user */
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $unreadMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient' => $user, 'isReader' => false]);

        $countMsg = count($unreadMessages);

        return $this->render("user/profile.html.twig", [
            'user' => $user,
            'countMsg' => $countMsg
        ]);
    }
}
