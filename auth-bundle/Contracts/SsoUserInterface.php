<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Contracts;

use Symfony\Component\Security\Core\User\UserInterface;

interface SsoUserInterface extends UserInterface
{
    public function getSsoId(): string;

    public function isEnabled(): bool;
}
