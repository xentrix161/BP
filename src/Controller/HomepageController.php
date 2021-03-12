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
    private $limitArticlesPerPage = 1;

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
     * @param int $pageNumber
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
        $outputArray = $this->pagination($pageNumber, $totalPages);
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

    private function pagination($pageNum, $totalPages)
    {
        $current = $pageNum;
        $last = $totalPages;
        $delta = 1; //pocet + - stranok od currentPage
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = array();
        $rangeWithDots = array();
        $l = -1;

        for ($i = 1; $i <= $last; $i++)
        {
            if ($i == 1 || $i == $last || $i >= $left && $i < $right)
            {
                array_push($range, $i);
            }
        }

        for($i = 0; $i<count($range); $i++)
        {
            if ($l != -1)
            {
                if ($range[$i] - $l === 2)
                {
                    array_push($rangeWithDots, $l + 1);
                }
                else if ($range[$i] - $l !== 1)
                {
                    array_push($rangeWithDots, 0);
                }
            }

            array_push($rangeWithDots, $range[$i]);
            $l = $range[$i];
        }

        return $rangeWithDots;
    }
}