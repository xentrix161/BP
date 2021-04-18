<?php

namespace App\Services;

use App\Entity\ShoppingCart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class ShoppingCartService extends AbstractController
{
    public const STATUS_DELETED = 'CART_DELETED';
    public const STATUS_PENDING = 'CART_PENDING';
    public const STATUS_PAYMENT = 'CART_PAYMENT';
    public const STATUS_DONE = 'CART_DONE';

    private $security;
    private $session;

    private $isAdmin = false;
    private $em = null;

    private $cart = null;
    private $variables = [];

    public function __construct(SessionInterface $session, Security $security)
    {
        $this->session = $session;
        $this->security = $security;

        $this->cart = new ShoppingCart();
    }

    public function asAdmin(): self
    {
        $this->isAdmin = true;
        return $this;
    }

    public function update(): void
    {
        $this->em = $this->getDoctrine()->getManager();
        if (empty($this->getValue('ownerId'))) {
            $this->reset();
            return;
        }

        $cart = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findOneBy(['user_id' => $this->getValue('ownerId'), 'status' => self::STATUS_PENDING]);

        if (empty($cart)) {
            $this->reset();
            return;
        }

        foreach ($this->variables as $value) {
            if (!is_null($value['value']) && method_exists($this->cart, $value['method'])) {
                $cart->{$value['method']}($value['value']);
            }
        }

        $this->em->persist($cart);
        $this->em->flush();
        $this->reset();
    }

    public function create(): void
    {
        if (empty($this->getValue('ownerId'))) {
            return;
        }

        $rowExist = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findOneBy(['status' => self::STATUS_PENDING, 'user_id' => $this->getValue('ownerId')]);

        if (!empty($rowExist)) {
            $userEmailSes = $this->security->getUser()->getUsername();
            $this->session->set($userEmailSes, $rowExist->getCartContent());
            return;
        }

        $this->em = $this->getDoctrine()->getManager();

        foreach ($this->variables as $value) {

            if (!empty($value['method']) && method_exists($this->cart, $value['method'])) {
                if (
                    empty($value['value'] && is_array($value['value']))
                    || (empty($value['value'] && is_numeric($value['value'])))
                    || !empty($value['value'])
                ) {
                    $this->cart->{$value['method']}($value['value']);
                }
            }
        }
        $this->cart->setStatus(self::STATUS_PENDING);
        $this->cart->setDate($this->getDate());

        $this->em->persist($this->cart);
        $this->em->flush();
        $this->reset();
    }

    public function setOwnerId(int $userId): self
    {
        $this->setValue('ownerId', $userId);
        $this->setMethod('ownerId', 'setUserId');
        return $this;
    }

    public function setContent(array $content): self
    {
        $this->setValue('content', $content);
        $this->setMethod('content', 'setCartContent');
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->setValue('status', $status);
        $this->setMethod('status', 'setStatus');
        return $this;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->setValue('totalPrice', $totalPrice);
        $this->setMethod('totalPrice', 'setTotalPrice');
        return $this;
    }

    public function getAllCarts(): array
    {
        if (empty($this->getValue('ownerId'))) {
            return [];
        }

        $statusArray = [self::STATUS_PENDING, self::STATUS_DONE, self::STATUS_PAYMENT];

        if ($this->isAdmin) {
            array_push($statusArray, self::STATUS_DELETED);
        }

        return $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findBy(['user_id' => $this->getValue('ownerId'), 'status' => $statusArray]);
    }

    public function getActualCartContent(): array
    {
        if (empty($this->getValue('ownerId'))) {
            return [];
        }
        $cart = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findOneBy(['user_id' => $this->getValue('ownerId'), 'status' => self::STATUS_PENDING]);
        return $cart->getCartContent();
    }

    private function reset(): void
    {
        $this->isAdmin = false;
        $this->cart = new ShoppingCart();
        foreach ($this->variables as $index => $value) {
            unset($this->variables[$index]);
        }
    }

    private function getDate(): \DateTime
    {
        return new \DateTime();
    }

    private function setValue($name, $value)
    {
        $this->variables[$name]['value'] = $value;
    }

    private function setMethod($name, $method)
    {
        $this->variables[$name]['method'] = $method;
    }

    private function getValue($name)
    {
        if (!empty($this->variables[$name]['value'])) {
            return $this->variables[$name]['value'];
        }
        return null;
    }

    private function getMethod($name)
    {
        if (!empty($this->variables[$name]['method']) && method_exists($this->cart, $this->variables[$name]['method'])) {
            return $this->variables[$name]['method'];
        }
        return null;
    }

    /**
     * Vráti zoznam itemov v nákupnom košíku.
     * @return object ["name" => $sessionName, "items" => array, "isEmpty" => true/false]
     */
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

    public function setSessionItem($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;

        if (empty($sessionItems)) {
            $sessionItems = [];
        }
        array_push($sessionItems, $itemId);
        $this->session->set($session->name, $sessionItems);

        return $sessionItems;
    }

    /**
     * Vráti ID tovaru a k nemu počet kusov v nákupnom košíku.
     * @return array ['article_id' => 'count']
     */
    public function countNumberOfUniqueItems()
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

    /**
     * Vymaže item z nákupného košíka podľa ID. V prípade, že z daného druhu tovaru je v košíku viac ako 1 kus,
     * (všetky itemy)
     * @param $itemId
     * @return array
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
        return ["success" => true, "id" => $itemId, "numberOfItems" => count($temp), 'cartItems' => $temp];
    }

    /**
     * Vymaže item z nákupného košíka podľa ID (jeden item).
     * @param $itemId
     * @return array
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
        return $sessionItems;
    }

    public function countNumberOfAllSoldItemsSorted()
    {
        $allDoneCarts = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findBy(['status' => $this::STATUS_DONE]);

        $countingArray= [];
        foreach ($allDoneCarts as $doneCart) {
            foreach ($doneCart->getCartContent() as $articleId) {
                if (empty($countingArray[$articleId])) {
                    $countingArray[$articleId] = 1;
                } else {
                    $countingArray[$articleId]++;
                }
            }
        }
        $countingArray = $this->sortAssociativeArrayByValues($countingArray);
        return $countingArray;
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