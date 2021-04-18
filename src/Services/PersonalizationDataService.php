<?php


namespace App\Services;


use App\Entity\Article;
use App\Entity\ShoppingCart;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonalizationDataService extends AbstractController
{
    public function getAllCarts()
    {
        return $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findBy(['status' => ShoppingCartService::STATUS_DONE]);
    }

    public function getAllUserCarts($userId)
    {
        return $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findBy(['user_id' => $userId, 'status' => ShoppingCartService::STATUS_DONE]);
    }

    public function getCartContent(ShoppingCart $cart)
    {
        return $cart->getCartContent();
    }

    public function getCountOfAllSoldArticles()
    {
        $countingArray = [];
        $carts = $this->getAllCarts();

        foreach ($carts as $cart) {
            $content = $this->getCartContent($cart);

            foreach ($content as $articleId) {
                if (empty($countingArray[$articleId])) {
                    $countingArray[$articleId] = 1;
                } else {
                    $countingArray[$articleId]++;
                }
            }
        }
        return $countingArray;
    }

    /**
     * @param ShoppingCart $cart
     * @param false $sort
     * @param string $sortType
     * @return array
     */
    public function getAssociativeArrayFromCartContent(ShoppingCart $cart, $sort = false, $sortType = 'DESC')
    {
        $countingArray = [];
        foreach ($cart->getCartContent() as $articleId) {
            if (empty($countingArray[$articleId])) {
                $countingArray[$articleId] = 1;
            } else {
                $countingArray[$articleId]++;
            }
        }
        if ($sort) {
            $countingArray = $this->sortAssociativeArrayByValues($countingArray, $sortType);
        }
        return $countingArray;
    }

    /**
     * 1
     * @param $userId
     * @return ShoppingCart|object|null
     */
    public function getLatestUserCart($userId)
    {
        $latestCart = $this->getDoctrine()->getRepository(ShoppingCart::class)
//            ->findOneBy(['user_id' => $userId, 'status' => ShoppingCartService::STATUS_DONE], ['id' => 'DESC']);
            ->findOneBy(['user_id' => $userId, 'status' => ShoppingCartService::STATUS_PENDING], ['id' => 'DESC']);

        if (!empty($latestCart)) {
            return $latestCart;
        }
        return null;
    }

    /**
     * 2
     * @param ShoppingCart $cart
     * @return array
     */
    public function sortCartByItemsCount(ShoppingCart $cart)
    {
        return $this->getAssociativeArrayFromCartContent($cart, true);
    }

    /**
     * 3
     * @param $associativeArray
     * @return array
     */
    public function getTopCategoriesArticles($associativeArray)
    {
        $category1 = $this->getDoctrine()->getRepository(Article::class)
            ->find(array_keys($associativeArray)[0])->getCatId();
        $category2 = $category1;
        $counter = 1;
        while ($category2 == $category1 && $counter <= count($associativeArray) - 1) {
            $category2 = $this->getDoctrine()->getRepository(Article::class)
                ->find(array_keys($associativeArray)[$counter])->getCatId();
            $counter++;
        }

        $allArticlesFilteredByCategories = $this->getDoctrine()->getRepository(Article::class)
            ->findBy(['cat_id' => [$category1, $category2]]);

        $filteredArray = [];
        foreach ($allArticlesFilteredByCategories as $article) {
            if (!in_array($article->getId(), array_keys($associativeArray))) {
                array_push($filteredArray, $article);
            }
        }
        return $filteredArray;
    }

//    public function getUniqueCategoriesFromArticleList($associativeArray)
//    {
//        $uniqueCategories = [];
//        $articleListKeys = array_keys($associativeArray);
//        $articleList = $this->getDoctrine()->getRepository(Article::class)
//            ->findBy(['id' => $articleListKeys]);
//
//        foreach ($articleList as $article) {
//            $catId = $article->getCatId();
//            if (!in_array($catId, $uniqueCategories)) {
//                array_push($uniqueCategories, $catId);
//            }
//        }
//        return $uniqueCategories;
//    }


    /**
     * 4
     * @return array
     */
    public function getAdvertisedArticles()
    {
        $user = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->getUser()->getUsername()]);

        if (empty($user)) {
            return [];
        }

        $userCart = $this->getLatestUserCart($user->getId());
        $userCartContent = $this->getAssociativeArrayFromCartContent($userCart, true);

        $array = $this->getTopCategoriesArticles($userCartContent);
        shuffle($array);
        return array_slice($array, 0, 6);
    }

    private function sortAssociativeArrayByValues($array, $sortType = 'DESC')
    {
        if ($sortType === 'ASC') {
            uasort($array, function ($a, $b) {
                return $a > $b;
            });
        } else {
            uasort($array, function ($a, $b) {
                return $a < $b;
            });
        }
        return $array;
    }
}