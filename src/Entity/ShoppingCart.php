<?php

namespace App\Entity;

use App\Repository\ShoppingCartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShoppingCartRepository::class)
 */
class ShoppingCart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $cart_content = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $total_price;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCartContent(): ?array
    {
        return $this->cart_content;
    }

    public function setCartContent(?array $cart_content): self
    {
        $this->cart_content = $cart_content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
