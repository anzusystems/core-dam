<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\Contracts\Entity\AnzuUser;
use App\Domain\User\UserManager;
use App\Entity\User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractFixtures<User>
 */
final class UserFixtures extends AbstractFixtures
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public static function getIndexKey(): string
    {
        return User::class;
    }

    public function load(ProgressBar $progressBar): void
    {
        $this->configureAssignedGenerator();
        foreach ($progressBar->iterate($this->getData()) as $user) {
            $user = $this->userManager->create($user);
            $this->addToRegistry($user, (int) $user->getId());
        }
    }

    /**
     * @return iterable<User>
     */
    private function getData(): iterable
    {
        $adminUser = (new User())
            ->setId(User::ID_ADMIN)
            ->setEmail('dam_admin@anzusystems.dev')
            ->setRoles([AnzuUser::ROLE_ADMIN])
            ->setEnabled(true)
        ;
        $password = $this->userPasswordHasher->hashPassword($adminUser, 'admin');
        $adminUser
            ->setPassword($password)
        ;

        yield $adminUser;
    }
}
