<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\User;
use App\Services\ChartsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryPanelController extends AbstractController
{
    private $chartService;

    public function __construct(ChartsService $chartService)
    {
        $this->chartService = $chartService;
    }

    /**
     * @Route("/category/panel", name="category_panel")
     */
    public function index(): Response
    {
//        $allCharts = $this->chartService->getTopCharts();
        $registeredUsers = $this->getNumberOfRegisteredUsers();
        $totalOrders = $this->getNumberOfTotalOrders();
        $allCategories = $this->getCategoryList();
        return $this->render('category_panel.html.twig', [
            'controller_name' => 'CategoryPanelController',
            'cat' => $allCategories,
//            'charts' => $allCharts,
            'registeredUsers' => $registeredUsers,
            'numberOfTotalOrders' => $totalOrders
        ]);
    }

    /**
     * Vráti všetky kategórie.
     * @return Category[]|array
     */
    public function getCategoryList()
    {
        $categoriesFromDB = $this->getDoctrine()
            ->getRepository(Category::class);
        return $categoriesFromDB->findAll();
    }

//    /**
//     * Vráti všetky TOP štatistiky.
//     * @return array[]|null
//     */
//    public function getTopCharts()
//    {
//        $outputArray = [
//            'TOP hodnotení predajci' => $this->getTopRatedSellers(),
//            'TOP hodnotené tovary' => $this->getTopArticles(),
//            'TOP profitový predajci' => $this->getTopEarners(),
////            'TOP predávané tovary' => $this->getTopSoldArticles()
//
//        ];
//
//        $empty = false;
//        foreach ($outputArray as $item) {
//            $empty = empty($item);
//        }
//        return $empty ? null : $outputArray;
//    }

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

}
