<?php


namespace App\Services;


class InputValidationService
{
    private $validateArray = [];
    private $flashMessage = "";

    public function validate(): bool
    {
        $returnOutput = true;
        if (empty($this->validateArray)) {
            return false;
        }

        foreach ($this->validateArray as $key => $value) {
            if ($value == false) {
                $this->flashMessage = $this->createInfoMessages($key, 'error');
                $returnOutput = false;
                break;
            }
        }
        return $returnOutput;
    }

    public function title(string $title): self
    {
        $this->validateArray['title'] = strlen($title) > 2;
        return $this;
    }

    public function description(string $description): self
    {
        $this->validateArray['description'] = strlen($title) > 5;
        return $this;
    }

    public function price(string $price): self
    {
        $price = str_replace(' ', '', $price);
        $this->validateArray['price'] = (int)$price > 0;
        return $this;
    }

    public function amount(string $amount): self
    {
        $amount = str_replace(' ', '', $amount);
        $this->validateArray['amount'] = (int)$amount >= 0;
        return $this;
    }

    public function slug(string $slug): self
    {
        //TODO
        $slug = str_replace(' ', '', $slug);
        $this->validateArray['slug'] = true;
        return $this;
    }

    public function profit(string $profit): self
    {
        $this->validateArray['profit'] = (int)$profit > 0;
        return $this;
    }

    public function orderId(string $orderId): self
    {
        $this->validateArray['order_id'] = (int)$orderId > 0;
        return $this;
    }

    public function address(string $address): self
    {
        //TODO
        $this->validateArray['address'] = true;
        return $this;
    }

    /**
     * Vygeneruje správu pre alert.
     * @return string
     */
    public function getMessage(): string
    {
        $temp = $this->flashMessage;
        $this->flashMessage = "";
        return $temp;
    }

    /**
     * Vytvorí príslušnú správu pre alert.
     * @param $fieldName
     * @param $infoType
     * @return string
     */
    private function createInfoMessages($fieldName, $infoType)
    {
        $messagesArray = [
            'title' => [
                'success' => '',
                'error' => 'Názov je príliš krátky. Musí obsahovať aspoň 3 znaky!'
            ],
            'description' => [
                'success' => '',
                'error' => 'Popis je príliš krátky. Musí obsahovať aspoň 5 znakov!'
            ],
            'price' => [
                'success' => '',
                'error' => 'Cena môže byť iba kladné, prípadne kladné desatinné číslo!'
            ],
            'amount' => [
                'success' => '',
                'error' => 'Množstvo môže byť iba kladné číslo, prípadne nula!'
            ],
            'slug' => [
                'success' => '',
                'error' => 'Slug môže obsahovať iba text prípadne text s pomlčkou!'
            ],
            'profit' => [
                'success' => '',
                'error' => 'Profit môže byť iba kladné číslo!'
            ],
            'order_id' => [
                'success' => '',
                'error' => 'Order ID môže byť iba kladné číslo!'
            ],
            'address' => [
                'success' => '',
                'error' => 'Adresa nie je v správnom tvare!'
            ]

        ];
        return $messagesArray[$fieldName][$infoType];
    }
}