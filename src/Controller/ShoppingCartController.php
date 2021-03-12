<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\Query\AST\JoinAssociationDeclaration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


/**
 * @Route("/shoppingcart")
 */
class ShoppingCartController extends AbstractController
{
    private $session;
    private $security;

    public function __construct(SessionInterface $session, Security $security)
    {
        $this->session = $session;
        $this->security = $security;
    }

    /**
     * @Route("/", name="shopping_cart")
     */
    public function index()
    {
        return $this->render('shopping_cart/index.html.twig', [
            'controller_name' => 'ShoppingCartController',
            'items' => $this->getItemsFromDbById(),
            'count' => $this->countNumberOfUniqueItems(),
            'totalPrice' => $this->getTotalPrice(),
            'isCartEmpty' => $this->getSessionItems()->isEmpty
        ]);
    }

    /**
     * @Route("/add/{itemId}", name="add_to_cart")
     * @param $itemId
     * @return JsonResponse
     */
    public function addToShoppingCart($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;

        if (empty($sessionItems)) {
            $sessionItems = [];
        }
        array_push($sessionItems, $itemId);
        $this->session->set($session->name, $sessionItems);

        return new JsonResponse($sessionItems);
    }

    /**
     * @Route("/delete/{itemId}", name="delete_from_cart")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteFromShoppingCart($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;

        if (!empty($sessionItems)) {
            $temp = [];
            $found = false;
            foreach ($sessionItems as $item) {
                if ($item == $itemId && $found == false) {
                    $found = true;
                } else {
                    array_push($temp, $item);
                }
            }
            $sessionItems = [];
            $sessionItems = array_merge($sessionItems, $temp);
        }
        $this->session->set($session->name, $sessionItems);
        return new JsonResponse($sessionItems);
    }

    /**
     * @Route("/delete-cart", name="delete_cart")
     */
    public function deleteFullShoppingCart()
    {
        $this->session->set($this->getSessionItems()->name, []);
        return new JsonResponse(["success" => true]);
    }

    /**
     * @Route("/delete-item/{itemId}", name="delete_item")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteItemFromShoppingCartById($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;
        $temp = [];
        foreach ($sessionItems as $item) {
            if ($item != $itemId) {
                array_push($temp, $item);
            }
        }
        $this->session->set($session->name, $temp);
        return new JsonResponse(["success" => true, "id" => $itemId, "numberOfItems" => count($temp)]);
    }

    /**
     * @Route("/get", name="get_cart")
     * @return JsonResponse
     */
    public function getShoppingCart()
    {
        return new JsonResponse($this->getSessionItems()->items);
    }

    public function getSessionItems()
    {
        $userEmailSes = $this->security->getUser()->getUsername();
        $sessionItems = $this->session->get($userEmailSes);

        if (empty($sessionItems)) {
            $sessionItems = [];
        }

        $array = ["name" => $userEmailSes, "items" => $sessionItems, "isEmpty" => empty($sessionItems)];
        return (object)$array;
    }

    private function countNumberOfUniqueItems()
    {
        $sessionItems = $this->getSessionItems()->items;
        $outputArray = [];
        foreach ($sessionItems as $item) {
            if (!empty($outputArray[$item])) {
                $outputArray[$item]++;
            } else {
                $outputArray[$item] = 1;
            }
        }
        return $outputArray;
    }

    private function getItemsFromDbById()
    {
        $itemsObject = $this->countNumberOfUniqueItems();

        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy(['id' => array_keys($itemsObject)]);
    }

    private function getTotalPrice()
    {
        $countedItems = $this->countNumberOfUniqueItems(); //na indexe je ID itemu a v poli je pocet itemov
        $uniqItems = $this->getItemsFromDbById();
        $total = 0;

        if (is_null($uniqItems)) {
            return 0;
        }

        foreach ($uniqItems as $uniqItem) {
            $id = (int)$uniqItem->getId();
            if (empty($id) && $id != 0) {
                continue;
            }
            $count = $countedItems[$id];
            $total += $count * $uniqItem->getPrice();
        }
        return $total;
    }


    //        // stores an attribute in the session for later reuse
//        $this->session->set('attribute-name', 'attribute-value');
//
//        // gets an attribute by name
//        $foo = $this->session->get('foo');
//
//        // the second argument is the value returned when the attribute doesn't exist
//        $filters = $this->session->get('filters', []);
//
//        // ...


}
