<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
//HOMEPAGE
    private $limitArticlesPerPage = 3;

    /**
     * @Route("/homepage/{pageNumber}", name="app_homepage")
     * @param $pageNumber
     * @return Response
     */
    public function index($pageNumber = 1): Response
    {
        if (!is_numeric($pageNumber) || $pageNumber < 1) {
            $pageNumber = 1;
        }
        $allArticles = $this->getArticleList($pageNumber);
        $allCategories = $this->getCategoryList();
        $totalPages = $this->generatePaginationBar($pageNumber);
        return $this->render('homepage.html.twig', [
            'controller_name' => 'HomepageController',
            'data' => $allArticles,
            'categories' => $allCategories,
            'totalPages' => $totalPages,
            'pageNumber' => $pageNumber
        ]);
    }

    /**
     * @Route("/homepage/category/{id}", name="app_homepage_category")
     * @param $id
     * @return Response
     */
    public function categoryIndex($id, $pageNumber = 1)
    {
        $allArticles = $this->getArticlesByCategoryId($id);
        $allCategories = $this->getCategoryList();
        $totalPages = $this->generatePaginationBar($pageNumber);
        return $this->render('homepage.html.twig', [
            'controller_name' => 'HomepageController',
            'data' => $allArticles,
            'categories' => $allCategories,
            'totalPages' => $totalPages,
            'pageNumber' => $pageNumber
        ]);
    }

    public function generatePaginationBar($pageNumber)
    {
        $totalPages = $this->getTotalPages();
//        $outputArray = [1, $pageNumber + 1, $pageNumber + 2, 0, $totalPages];
        $outputArray = [1, 0, $pageNumber - 1, $pageNumber, $pageNumber + 1, 0, $totalPages];
        return $outputArray;
    }

    public function getArticlesByCategoryId($categoryId)
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy(["category_id" => $categoryId]);
    }

    public function getArticleList($pageNumber = 1)
    {
        $offset = ($pageNumber - 1) * $this->limitArticlesPerPage;
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy([], [], $this->limitArticlesPerPage, $offset);
    }

    public function getTotalPages()
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $all = $articlesFromDB->findAll();
        $totalNumberOfArticles = count($all);
        return ceil($totalNumberOfArticles / $this->limitArticlesPerPage);
    }

    public function getCategoryList()
    {
        $categoriesFromDB = $this->getDoctrine()
            ->getRepository(Category::class);
        return $categoriesFromDB->findAll();
    }

    public function getCombineList()
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $all = $articlesFromDB->findAll();

        foreach ($all as $item) {
            $item->getCategoryId();
        }
//        $categoriesFromDB = $this->getDoctrine()
//            ->getRepository(Category::class);
    }
}