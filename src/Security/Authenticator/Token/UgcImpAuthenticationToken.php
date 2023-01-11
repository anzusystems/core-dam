<?php

declare(strict_types=1);

namespace App\Security\Authenticator\Token;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final class UgcImpAuthenticationToken extends PostAuthenticationToken
{
    public function __construct(
        readonly UserInterface $user,
        readonly string $firewallName,
        readonly array $roles,
        readonly private User $originalUser
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public function getOriginalUser(): User
    {
        return $this->originalUser;
    }
}
