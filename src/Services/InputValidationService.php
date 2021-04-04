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
        $this->validateArray['title'] = strlen($title) >= 3;
        return $this;
    }

    public function description(string $description): self
    {
        $this->validateArray['description'] = strlen($description) >= 6;
        return $this;
    }

    public function price(string $price): self
    {
        $price = str_replace(' ', '', $price);
        $this->validateArray['price'] = (double)$price > 0;
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
        if (strlen(str_replace(' ', '', $slug)) < 3) {
            return $this;
        }

        $slug = str_replace(' ', '-', $slug);
        $slug = $this->normalize($slug);
        $this->validateArray['slug'] = strlen($slug) >= 3;
        return $this;
    }

    public function profit(string $profit): self
    {
        $this->validateArray['profit'] = (double)$profit > 0;
        return $this;
    }

    public function orderId(string $orderId): self
    {
        $this->validateArray['order_id'] = (int)$orderId > 0;
        return $this;
    }

//    public function address(string $address): self
//    {
//        //$special = preg_match('@[$-/:-?<>{-~!"^_`\[\]]@', $surname);
//        $this->validateArray['address'] = true;
//        return $this;
//    }

    public function city(string $city): self
    {

        //TODO
        $form = preg_match('@([a-zA-Z. ľščťžýáíé])@', $city);

        $this->validateArray['city'] = $form;
        return $this;
    }

    public function zip(string $zip): self
    {
        //TODO
    }

    public function street(string $street): self
    {
        //TODO
    }


    public function mobileNumber(string $mobileNumber): self
    {
        //TODO
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
            'city' => [
                'success' => '',
                'error' => 'Mesto nie je v správnom tvare!'
            ],
            'zip' => [
                'success' => '',
                'error' => 'PSČ nie je v správnom tvare!'
            ],
            'street' => [
                'success' => '',
                'error' => 'Ulica nie je v správnom tvare!'
            ],
            'mobileNumber' => [
                'success' => '',
                'error' => 'Mobil nie je v správnom tvare!'
            ]

        ];
        return $messagesArray[$fieldName][$infoType];
    }

    function normalize ($string) {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c',
            'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A',
            'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',  'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
            'Ľ' => 'L', 'Ĺ' => 'L', 'ľ' => 'l', 'ĺ' => 'l', 'Ť' => 'T', 'ť' => 't', 'Ď' => 'D', 'ď' => 'd', 'Ň' => 'N', 'ň' => 'n', 'Ř' => 'R', 'ř' => 'r', 'Ů' => 'U', 'ů' => 'r',

        );

        return strtr($string, $table);
    }
}