<?php


namespace App\Services;


use App\Entity\ShoppingCart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShoppingCartService extends AbstractController
{
    public const STATUS_DELETED = 'CART_DELETED';
    public const STATUS_PENDING = 'CART_PENDING';
    public const STATUS_PAYMENT = 'CART_PAYMENT';
    public const STATUS_DONE = 'CART_DONE';

    private $isAdmin = false;
    private $ownerId = null;
    private $content = null;
    private $status = null;
    private $totalPrice = null;

    public function asAdmin(): self
    {
        $this->isAdmin = true;
        return $this;
    }

    public function update(): void
    {
        if (is_null($this->ownerId)) {
            $this->reset();
            return;
        }

        $cart = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findOneBy(['user_id' => $this->ownerId, 'status' => self::STATUS_PENDING]);

        if (empty($cart)) {
            $this->reset();
            return;
        }

        if (!is_null($this->content)) {
            $cart->setCartContent($this->content);
        }

        if (!is_null($this->status)) {
            $cart->setStatus($this->status);
        }

        if (!is_null($this->totalPrice)) {
            $cart->setTotalPrice($this->totalPrice);
        }

        if ($this->isAdmin) {
            $cart->setUserId($this->ownerId);
        }

        //TODO: updatne existujuci zaznam
        $this->reset();
    }

    public function create(): void
    {
        $shoppingCart = new ShoppingCart();
        //TODO: vytovri novy zaznam
        $this->reset();
    }

    public function setOwnerId(int $userId): self
    {
        //TODO: nasetuje ID vlastnika kosiku
        return $this;
    }

    public function setContent(array $content): self
    {
        //TODO: nasetuje article do kosiku
        return $this;
    }

    public function setStatus(string $status): self
    {
        //TODO: nasetuje status kosika
        return $this;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        //TODO: nasetuje celkovu cenu za kosik
        return $this;
    }

    public function getAllCarts(): array
    {
        //TODO: vrati vsetky kosiky podla setnuteho ID usera, ak je admin vrati aj DELETED kosiky, ak nie je tak ostatne
        return [];
    }

    public function getContent(): array
    {
        //TODO: vrati obsah konkretneho kosika
        return [];
    }

    private function reset(): void
    {
        $this->isAdmin = false;
        $this->ownerId = null;
        $this->content = null;
        $this->status = null;
        $this->totalPrice = null;
    }

    private function getDate(): \DateTime
    {
        return new \DateTime();
    }




}