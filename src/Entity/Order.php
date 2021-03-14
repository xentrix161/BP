<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`Order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="float")
     */
    private $total_price;

    /**
     * @ORM\Column(type="integer")
     */
    private $invoice_number;

    /**
     * @ORM\Column(type="boolean")
     */
    private $payment_method;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paid;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
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

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getInvoiceNumber(): ?int
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(int $invoice_number): self
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getPaymentMethod(): ?bool
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(bool $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }
}
