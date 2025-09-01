<?php

declare(strict_types=1);

namespace App\Security\Authenticator\Token;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final class UgcImpAuthenticationToken extends PostAuthenticationToken
{
    public function __construct(
        public readonly UserInterface $user,
        public readonly string $firewallName,
        public readonly array $roles,
        private readonly User $originalUser
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public function getOriginalUser(): User
    {
        return $this->originalUser;
    }
}
