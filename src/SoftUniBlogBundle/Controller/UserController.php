<?php

namespace SoftUniBlogBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\Role;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()){

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            if($file != null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('article_directory'),
                    $fileName);
                $user->setImage($fileName);
            }else{
                $defName= "user_profile.png";
                $user->setImage($defName);
            }


            $password = $this
                ->get('security.password_encoder')
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

        return $this->render('user/register.html.twig', ['form' =>$form->createView()]);
    }


    /**
     * @Route("/profile/edit/{id}", name="profile_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {

        $currentUser = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if ($currentUser === null) {
            return $this->redirectToRoute("home_index");
        }

        $form = $this->createForm(UserType::class, $currentUser);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            if($file != null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('article_directory'),
                    $fileName);
                $currentUser->setImage($fileName);
            }else{
                $defName= "user_profile.png";
                $currentUser->setImage($defName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();

            return $this->redirectToRoute("home_index");
        }
        return $this->render('user/edit.html.twig',
            ['form' => $form->createView(),
                'currentUser' => $currentUser]);
    }


    /**
     * @Route("/profile/delete/{id}", name="profile_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $id)
    {
        $currentUser = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if ($currentUser === null) {
            return $this->redirectToRoute("home_index");
        }

        $form = $this->createForm(UserType::class, $currentUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($currentUser);
            $em->flush();

            return $this->redirectToRoute("home_index");
        }
        return $this->render('user/delete.html.twig',
            ['form' => $form->createView(),
                'currentUser' => $currentUser]);
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(){

        $userId = $this->getUser()->getId();

        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);

        $unreadMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient'=> $user, 'isReader' => false]);

        $countMsg = count($unreadMessages);

        return $this->render("user/profile.html.twig", ['user' => $user, 'countMsg' => $countMsg]);
    }

}
