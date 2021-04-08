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
    private $em = null;

    private $variables = [
        'ownerId' => ['value' => null, 'method' => 'setUserId'],
        'content' => ['value' => null, 'method' => 'setCartContent'],
        'status' => ['value' => null, 'method' => 'setStatus'],
        'totalPrice' => ['value' => null, 'method' => 'setTotalPrice'],
    ];

    public function __construct()
    {
        $this->em = $this->getDoctrine()->getManager();
    }

    public function asAdmin(): self
    {
        $this->isAdmin = true;
        return $this;
    }

    public function update(): void
    {
        if (is_null($this->variables['ownerId']['value'])) {
            $this->reset();
            return;
        }

        $cart = $this->getDoctrine()->getRepository(ShoppingCart::class)
            ->findOneBy(['user_id' => $this->variables['ownerId']['value'], 'status' => self::STATUS_PENDING]);

        if (empty($cart)) {
            $this->reset();
            return;
        }

        foreach ($this->variables as $value) {
            if (!is_null($value['value'])) {
                $cart->{$value['method']}($value['value']);
            }
        }

        $this->em->persist($cart);
        $this->em->flush();
        $this->reset();
    }

    public function create(): void
    {
        $cart = new ShoppingCart();
        if ($this->isSomeOfAttrNull()) {
            return;
        }

        foreach ($this->variables as $value) {
            $cart->{$value['method']}($value['value']);
        }
        $cart->setStatus(self::STATUS_PENDING);
        $cart->setDate($this->getDate());

        $this->em->persist($cart);
        $this->em->flush();
        $this->reset();
    }

    public function setOwnerId(int $userId): self
    {
        $this->variables['ownerId']['value'] = $userId;
        return $this;
    }

    public function setContent(array $content): self
    {
        $this->variables['content']['value'] = $content;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->variables['status']['value'] = $status;
        return $this;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->variables['totalPrice']['value'] = $totalPrice;
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
        foreach ($this->variables as $value) {
            $value['value'] = null;
        }
    }

    private function getDate(): \DateTime
    {
        return new \DateTime();
    }

    private function isSomeOfAttrNull(): bool
    {
        foreach ($this->variables as $value) {
            if (is_null($value['value'])) {
                $this->reset();
                return true;
            }
        }
        return false;
    }
}