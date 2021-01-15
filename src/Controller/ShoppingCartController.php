<?php

namespace App\Controller;

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
        ]);
    }

    /**
     * @Route("/add/{item_id}", name="add_to_cart")
     * @param $item_id
     * @return JsonResponse
     */
    public function addToShoppingCart($item_id)
    {
        $userEmailSes = $this->security->getUser()->getUsername();
        //getUsername() vracia mail, override interface
        $sessionItems = $this->session->get($userEmailSes);

        if (empty($sessionItems)) {
            $sessionItems = [];
        }
        array_push($sessionItems, $item_id);
        $this->session->set($userEmailSes, $sessionItems);

        return new JsonResponse($sessionItems);
    }

    /**
     * @Route("/delete/{item_id}", name="delete_from_cart")
     * @param $item_id
     * @return JsonResponse
     */
    public function deleteFromShoppingCart($item_id)
    {
        $userEmailSes = $this->security->getUser()->getUsername();
        $sessionItems = $this->session->get($userEmailSes);

        if (!empty($sessionItems)) {
            $temp = [];
            $found = false;
            foreach ($sessionItems as $item) {
                if ($item == $item_id && $found == false) {
                    $found = true;
                } else {
                    array_push($temp, $item);
                }
            }
            $sessionItems = [];
            $sessionItems = array_merge($sessionItems, $temp);
        }
        $this->session->set($userEmailSes, $sessionItems);

        return new JsonResponse($sessionItems);
    }


    /**
     * @Route("/get", name="get_cart")
     * @return JsonResponse
     */
    public function getShoppingCart() {
        $userEmailSes = $this->security->getUser()->getUsername();
        $sessionItems = $this->session->get($userEmailSes);
        return new JsonResponse($sessionItems);
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
