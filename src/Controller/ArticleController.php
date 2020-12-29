<?php

namespace App\Controller;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/{id}", name="article")
     * @param $id
     */
    public function getArticle($id)
    {
        $article = new Article();
        //cela tabulka articlov
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        dump($articlesFromDB->findAll());
        $data = $articlesFromDB->findOneBy(['id' => $id]);

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'data' => $data
        ]);
    }
}
