<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\Contracts\Entity\AnzuPermissionGroup;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use App\Entity\User;
use AnzuSystems\CoreDamBundle\Entity\PermissionGroup;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('anzu_systems_serializer', [
        'parameter_bag' => [
            DamUser::class => User::class,
            AnzuUser::class => User::class,
            AnzuPermissionGroup::class => PermissionGroup::class,
        ],
    ]);
};
