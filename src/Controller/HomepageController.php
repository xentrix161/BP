<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Services\RoleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomepageController extends AbstractController
{
//HOMEPAGE
    private $limitArticlesPerPage = 3;
    private $security;
    private $roleService;

    public function __construct(Security $security, RoleService $roleService)
    {
        $this->security = $security;
        $this->roleService = $roleService;
    }

    /**
     * @Route("/homepage/{pageNumber}", name="app_homepage")
     * @param $pageNumber
     * @return Response
     */
    public function index($pageNumber = 1): Response
    {
        if (!$this->roleService->isNot($this->roleService::ROLE_NONE)) {
            return $this->redirectToRoute('app_role');
        }

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
            'pageNumber' => $pageNumber,
            'categoryBool' => false
        ]);
    }

    /**
     * @Route("/homepage/category/{id}/{pageNumber}", name="app_homepage_category")
     * @param $id
     * @param int $pageNumber
     * @return Response
     */
    public function categoryIndex($id, $pageNumber = 1)
    {
        if (!$this->roleService->isNot($this->roleService::ROLE_NONE)) {
            return $this->redirectToRoute('app_role');
        }


        if (!is_numeric($pageNumber) || $pageNumber < 1) {
            $pageNumber = 1;
        }
        $allArticles = $this->getArticlesByCategoryId($id, $pageNumber);
        $allCategories = $this->getCategoryList();
        $totalPages = $this->generatePaginationBarForCategories($pageNumber, $id);
        return $this->render('homepage.html.twig', [
            'controller_name' => 'HomepageController',
            'data' => $allArticles,
            'categories' => $allCategories,
            'totalPages' => $totalPages,
            'pageNumber' => $pageNumber,
            'id' => $id,
            'categoryBool' => true
        ]);
    }

    /**
     * @Route("/access-denied", name="access_denied")
     */
    public function accessDeniedPage()
    {
        return $this->render('access_denied.html.twig');
    }

    /**
     * @Route("/roles", name="app_role")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function chooseRole(Request $request)
    {
        $user = $this->security->getUser();
        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }

        $selection = $request->get('selection');
        if (!empty($selection)) {

            $em = $this->getDoctrine()->getManager();
            $userToUpadate = $em->getRepository(User::class)->findOneBy([
                'email' => $user->getUsername()
            ]);

            $userToUpadate->setRole([$selection]);
            $em->flush();
            return $this->redirectToRoute('app_homepage');
        }
        return $this->render('choose_role.html.twig');
    }

    public function generatePaginationBar($pageNumber)
    {
        $totalPages = $this->getTotalPages();
        return $this->pagination($pageNumber, $totalPages);
    }

    public function generatePaginationBarForCategories($pageNumber, $category_id)
    {
        $totalPages = $this->getTotalPagesForCategories($category_id);
        return $this->pagination($pageNumber, $totalPages);
    }

    public function getArticlesByCategoryId($categoryId, $pageNumber = 1)
    {
        $offset = ($pageNumber - 1) * $this->limitArticlesPerPage;
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy(["cat_id" => $categoryId], [], $this->limitArticlesPerPage, $offset);
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

    public function getTotalPagesForCategories($categoryId)
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $all = $articlesFromDB->findBy(['cat_id' => $categoryId]);
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
    }

    private function pagination($pageNum, $totalPages)
    {
        $current = $pageNum;
        $last = $totalPages;
        $range = array();
        $rangeWithDots = array();

        $delta = 1; //pocet + - stranok od currentPage
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $l = -1;

        for ($i = 1; $i <= $last; $i++) {
            if ($i == 1 || $i == $last || $i >= $left && $i < $right) {
                array_push($range, $i);
            }
        }

        for ($i = 0; $i < count($range); $i++) {
            if ($l != -1) {
                if ($range[$i] - $l === 2) {
                    array_push($rangeWithDots, $l + 1);
                } else if ($range[$i] - $l !== 1) {
                    array_push($rangeWithDots, 0);
                }
            }
            array_push($rangeWithDots, $range[$i]);
            $l = $range[$i];
        }
        return $rangeWithDots;
    }
}