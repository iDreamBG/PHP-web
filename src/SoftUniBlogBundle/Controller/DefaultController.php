<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home_index")
     */
    public function indexAction()
    {

        return $this->render('home/home.html.twig');
    }

    /**
     * @Route("/blog", name="blog_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function BlogAction(Request $request)
    {
        $articles = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findBy([], ['dateAdded' => 'desc']);

        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
        6/*limit per page*/
        );

        return $this->render('default/index.html.twig',
            ['pagination' => $pagination]);
    }
}
