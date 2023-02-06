<?php

declare(strict_types=1);

namespace App;

use AnzuSystems\CommonBundle\Kernel\AnzuKernel;
use App\DependencyInjection\CoreDamExtension;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends AnzuKernel
{
    use MicroKernelTrait;

    use MicroKernelTrait;
    protected int $userIdConsole = User::ID_CONSOLE;
    protected int $userIdAnonymous = User::ID_ANONYMOUS;
    protected int $userIdAdmin = User::ID_ADMIN;

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->registerExtension(new CoreDamExtension());
    }
}
