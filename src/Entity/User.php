<?php

declare(strict_types=1);


namespace App\Entity;

use AnzuSystems\CoreDamBundle\Entity\DamUser;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends DamUser
{
    public const ID_ANONYMOUS = 1_763_600;
    public const ID_CONSOLE = 1_000_000;
}