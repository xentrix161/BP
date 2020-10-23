<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    /**
     * @Route("/homepage")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("/1")
     * @return Response
     */
    public function index1(): Response
    {
        return $this->render('article1.html.twig');
    }

    /**
     * @Route("/2")
     * @return Response
     */
    public function index2(): Response
    {
        return $this->render('article2.html.twig');
    }

    /**
     * @Route("/3")
     * @return Response
     */
    public function index3(): Response
    {
        return $this->render('article3.html.twig');
    }

    /**
     * @Route("/4")
     * @return Response
     */
    public function index4(): Response
    {
        return $this->render('article4.html.twig');
    }

    /**
     * @Route("/5")
     * @return Response
     */
    public function index5(): Response
    {
        return $this->render('article5.html.twig');
    }

}
