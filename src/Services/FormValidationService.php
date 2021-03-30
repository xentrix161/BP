<?php


namespace App\Services;


class FormValidationService
{
    private $nameSurnameLength = 3;
    private $validateArray = [];
    private $flashMessage = "";

    /**
     * Vráti TRUE ak zadané kontrolné funkcie splnili podmienky.
     * @return bool
     */
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


    /**
     * Overí, či meno používateľa spĺňa podmienky.
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $number = preg_match('@[0-9]@', $name);
        $special = preg_match('@[$-/:-?<>{-~!"^_`\[\]]@', $name);
        $name = str_replace(' ', '', $name);
        $this->validateArray['name'] = !(strlen($name) < $this->nameSurnameLength || $number || $special);
        return $this;
    }

    /**
     * Overí, či priezvisko používateľa spĺňa podmienky.
     * @param string $surname
     * @return $this
     */
    public function surname(string $surname): self
    {
        $number = preg_match('@[0-9]@', $surname);
        $special = preg_match('@[$-/:-?<>{-~!"^_`\[\]]@', $surname);
        $surname = str_replace(' ', '', $surname);
        $this->validateArray['surname'] = !(strlen($surname) < $this->nameSurnameLength || $number || $special);
        return $this;
    }

    /**
     * Overí, či email používateľa spĺňa podmienky.
     * @param string $email
     * @return $this
     */
    public function email(string $email): self
    {
        $special = preg_match('/([a-z0-9._%+-]+)@([a-z0-9.-]+)\.[a-z]{2,63}$/', strtolower($email), $match);
        $this->validateArray['email'] = $special && strlen($match[1]) > 2;
        return $this;
    }


    /**
     * Overí, či heslo používateľa spĺňa minimálnu dlžku.
     * @param string $password
     * @param int $pwLength
     * @return $this
     */
    public function passwordLength(string $password, $pwLength = 6): self
    {
        $this->validateArray['passwordLength'] = strlen($password) >= $pwLength;
        return $this;
    }

    /**
     * Overí, či heslo používateľa obsahuje predpísané znaky.
     * @param string $password
     * @return $this
     */
    public function passwordChars(string $password): self
    {
        $lowerCase = preg_match('@[a-z]@', $password);
        $upperCase = preg_match('@[A-Z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $this->validateArray['passwordChars'] = ($lowerCase && $upperCase && $number);
        return $this;
    }

    /**
     * Overí, či sa heslo používateľa zhoduje s kontrolným heslom.
     * @param string $password1
     * @param string $password2
     * @return $this
     */
    public function passwordMatch(string $password1, string $password2): self
    {
        $this->validateArray['passwordMatch'] = ($password1 == $password2);
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
    private function createInfoMessages($fieldName, $infoType) {
        $messagesArray = [
            'name' => [
                'success' => '',
                'error' => 'Meno je príliš krátke. Musí obsahovať aspoň 3 znaky!'
            ],
            'surname' => [
                'success' => '',
                'error' => 'Priezvisko je príliš krátke. Musí obsahovať aspoň 3 znaky!'
            ],
            'email' => [
                'success' => '',
                'error' => 'Váš email je zadaný v nesprávnom tvare!'
            ],
            'passwordLength' => [
                'success' => '',
                'error' => 'Heslo je príliš krátke. Musí obsahovať aspoň 6 znakov!'
            ],
            'passwordChars' => [
                'success' => '',
                'error' => 'Heslo musí obsahovať aspoň 1 veľké písmeno, 1 malé písmeno a jedno číslo!'
            ],
            'passwordMatch' => [
                'success' => '',
                'error' => 'Vaše heslo sa nezhoduje s kontrolným heslom. Heslá sa musia zhodovať!'
            ]
        ];
        return $messagesArray[$fieldName][$infoType];
    }
}