<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Message;
use BlogBundle\Entity\User;
use BlogBundle\Form\MessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @Route("/user/{id}/message", name="add_message")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function addMessage(Request $request, $id)
    {
//        $articleIdFromRequest = substr($_SERVER['HTTP_REFERER'],
//            strrpos($_SERVER['HTTP_REFERER'], '/') + 1);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var User $recipient */
        $recipient = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $message
                ->setSender($currentUser)
                ->setRecipient($recipient)
                ->setIsReader(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $this->addFlash("message", "Message sent successfully!");

            return $this->redirectToRoute("add_message", [
                'id'=> $id
            ]);
        }

        return $this->render('user/send_message.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/mailbox", name="user_mailbox")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailBox(Request $request)
    {
        $currentUserId = $this->getUser()->getId();

        /** @var User $user */
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($currentUserId);

        $messages = $user->getRecipientMessages();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $messages, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );

        return $this->render("user/mailbox.html.twig", [
            'pagination' => $pagination
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @Route("/user/mailbox/message/{id}", name="send_answer")
     */
    public function messageAction(Request $request, $id)
    {
        /** @var Message $message */
        $message = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);

        $message->setIsReader(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        $sendMessage = new Message();
        $form = $this->createForm(MessageType::class, $sendMessage);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $sendMessage
                ->setSender($this->getUser())
                ->setRecipient($message->getSender())
                ->setIsReader(false);

            $em->persist($sendMessage);
            $em->flush();

            $this->addFlash("message", "Message sent successfully!");

            return $this->redirectToRoute("user_mailbox_current_message", [
                'id'=> $id
            ]);
        }


        return $this->render("user/message.html.twig", [
            'message' => $message,
            'form' => $form->createView()
        ]);
    }
}
