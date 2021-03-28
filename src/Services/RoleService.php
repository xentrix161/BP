<?php


namespace App\Services;


use Symfony\Component\Security\Core\Security;

class RoleService
{


    public const ROLE_USER = "ROLE_USER";
    public const ROLE_ADMIN = "ROLE_ADMIN";
    public const ROLE_SELLER = "ROLE_SELLER";
    public const ROLE_NONE = "ROLE_NONE";
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Vráti TRUE ak USER neexistuje, nemá rolu, alebo jeho rola nesedí s rolou v parametri funkcie.
     * @param string $role
     * @return bool
     */
    public function isNot(string $role = self::ROLE_NONE): bool
    {
        $user = $this->security->getUser();
        return (is_null($user) || empty($user->getRoles()[0]) || $user->getRoles()[0] != $role);
    }
}