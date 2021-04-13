<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Order;
use App\Entity\Rating;
use App\Entity\Shop;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    /**
     * @Route("/admin/statistics", name="statistics")
     */
    public function index(): Response
    {
        $totalProfit = $this->getTotalProfit();
        $totalOrders = $this->getNumberOfTotalOrders();
        $registeredUsers = $this->getNumberOfRegisteredUsers();
        return $this->render('statistics/index.html.twig', [
            'controller_name' => 'StatisticsController',
            'totalProfit' => $totalProfit,
            'totalOrders' => $totalOrders,
            'totalUsers' => $registeredUsers

        ]);
    }

    /**
     * @Route("/admin/statistics/article-ratings", name="statistics_article_ratings")
     */
    public function articleRatings(): Response
    {
        $articles = $this->getArticlesSortedByRating();
        return $this->render('statistics/article_ratings.html.twig', [
            'controller_name' => 'StatisticsController',
            'articlesSortedRating' => $articles
        ]);
    }

    /**
     * @Route("/admin/statistics/article-sold", name="statistics_article_sold")
     */
    public function articleSold(): Response
    {
        $articles = $this->getArticlesSortedBySold();
        return $this->render('statistics/article_sold.html.twig', [
            'controller_name' => 'StatisticsController',
            'articlesSortedSold' => $articles
        ]);
    }

    /**
     * @Route("/admin/statistics/portal-profit", name="statistics_portal_profit")
     */
    public function portalProfit(): Response
    {
        $profits = $this->getPortalProfits();
        return $this->render('statistics/portal_profit.html.twig', [
            'controller_name' => 'StatisticsController',
            'listOfProtis' => $profits
        ]);
    }

    /**
     * @Route("/admin/statistics/users-earners", name="statistics_users_earners")
     */
    public function usersEarners(): Response
    {
        $descEarners = $this->getUsersSortedByEarning();
        return $this->render('statistics/users_earners.html.twig', [
            'controller_name' => 'StatisticsController',
            'earners' => $descEarners
        ]);
    }

    /**
     * @Route("/admin/statistics/users-spenders", name="statistics_users_spenders")
     */
    public function usersSpenders(): Response
    {
        $descSpenders = $this->getUsersSortedByExpense();
        return $this->render('statistics/users_spenders.html.twig', [
            'controller_name' => 'StatisticsController',
            'spenders' => $descSpenders
        ]);
    }

    /**
     * @Route("/admin/statistics/users-ratings", name="statistics_users_ratings")
     */
    public function usersRatings(): Response
    {
        $descRatings = $this->getUsersSortedByRating();
        return $this->render('statistics/users_ratings.html.twig', [
            'controller_name' => 'StatisticsController',
            'ratingsUsers' => $descRatings
        ]);
    }


    public function getNumberOfRegisteredUsers()
    {
        $registeredUsers = $this->getDoctrine()->getRepository(User::class)
            ->findAll();

        return count($registeredUsers);
    }

    public function getNumberOfTotalOrders()
    {
        $totalOrders = $this->getDoctrine()->getRepository(Order::class)
            ->findAll();
        return count($totalOrders);
    }

    public function getTotalProfit()
    {
        $profitRecords = $this->getDoctrine()->getRepository(Shop::class)
            ->findAll();

        $profit = 0;

        foreach ($profitRecords as $record) {
            $profit += $record->getProfit();
        }
        return $profit;
    }

    public function getArticlesSortedByRating()
    {
        return $this->getDoctrine()->getRepository(Article::class)
            ->findBy(array(), array('rating' => 'DESC'));
    }

    public function getArticlesSortedBySold()
    {
        return $this->getDoctrine()->getRepository(Article::class)
            ->findBy(array(), array('sold' => 'DESC'));
    }

    public function getPortalProfits()
    {
        return $this->getDoctrine()->getRepository(Shop::class)
            ->findAll();
    }

    public function getUsersSortedByRating()
    {
        return $this->getDoctrine()->getRepository(User::class)
            ->findBy(array(), array('rating' => 'DESC'));
    }

    public function getUsersSortedByEarning()
    {
        return $this->getDoctrine()->getRepository(User::class)
            ->findBy(array(), array('earning' => 'DESC'));
    }

    public function getUsersSortedByExpense()
    {
        return $this->getDoctrine()->getRepository(User::class)
            ->findBy(array(), array('expense' => 'DESC'));
    }


    /**
     * @Route("/admin/statistics/users-all", name="statistics_users_all")
     */
    public function getAllUsers()
    {
        $listOfUsers = $this->getDoctrine()->getRepository(User::class)
            ->findAll();

        return $this->render('statistics/users_all.html.twig', [
            'controller_name' => 'StatisticsController',
            'listOfUsers' => $listOfUsers
        ]);
    }

    /**
     * @Route("/admin/statistics/orders-all", name="statistics_orders_all")
     */
    public function getAllOrders()
    {
        $listOfOrders = $this->getDoctrine()->getRepository(Order::class)
            ->findAll();

        return $this->render('statistics/orders_all.html.twig', [
            'controller_name' => 'StatisticsController',
            'listOfOrders' => $listOfOrders
        ]);
    }

    /**
     * @Route("/admin/statistics/ratings-all", name="statistics_ratings_all")
     */
    public function getAllRatings()
    {
        $listOfRatings = $this->getDoctrine()->getRepository(Rating::class)
            ->findAll();

        return $this->render('statistics/ratings_all.html.twig', [
            'controller_name' => 'StatisticsController',
            'listOfRatings' => $listOfRatings
        ]);
    }
}
