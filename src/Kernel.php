<?php

namespace App;

use AnzuSystems\CommonBundle\Kernel\AnzuKernel;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

class Kernel extends AnzuKernel
{
    protected int $userIdConsole = User::ID_CONSOLE;
    protected int $userIdAnonymous = User::ID_ANONYMOUS;
    protected int $userIdAdmin = User::ID_ADMIN;

    use MicroKernelTrait;
}
