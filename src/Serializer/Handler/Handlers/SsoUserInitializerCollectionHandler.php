<?php

declare(strict_types=1);

namespace App\Serializer\Handler\Handlers;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\SerializerBundle\Context\SerializationContext;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Handler\Handlers\AbstractHandler;
use AnzuSystems\SerializerBundle\Metadata\Metadata;
use App\Domain\User\UgcUserManager;
use App\Entity\User;
use App\HttpClient\Sso\SsoUserClientInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class SsoUserInitializerCollectionHandler extends AbstractHandler
{
    public function __construct(
        private readonly SsoUserClientInterface $ssoUserClient,
        private readonly UserRepository $userRepository,
        private readonly UgcUserManager $userManager,
    ) {
    }

    public function serialize(mixed $value, Metadata $metadata, SerializationContext $context): ?array
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Collection) {
            return $value->map(static fn (User $user): int => (int) $user->getId())->getValues();
        }

        throw new SerializerException('Unsupported value for ' . self::class . '::' . __FUNCTION__);
    }

    /**
     * @throws UnsuccessfulUserInfoRequestException
     * @throws UnsuccessfulAccessTokenRequestException
     * @throws SerializerException
     */
    public function deserialize(mixed $value, Metadata $metadata): ?ArrayCollection
    {
        if (null === $value) {
            return null;
        }

        /** @var ArrayCollection<int, AnzuUser> $users */
        $users = new ArrayCollection();
        $updatedSome = false;
        if (is_array($value)) {
            foreach ($value as $id) {
                /** @var User|null $user */
                $user = $this->userRepository->findOneBySsoUserId((string) $id);
                if ($user && $user->hasNotRole(User::ROLE_UGC)) {
                    $user->addRole(User::ROLE_UGC);
                    $updatedSome = true;
                }
                if (null === $user) {
                    $user = $this->userManager->createFromSsoUserInfo(
                        $this->ssoUserClient->getSsoUserInfo((string) $id)
                    );
                    $updatedSome = true;
                }
                $users->add($user);
            }
            if ($updatedSome) {
                $this->userManager->flush();
            }

            return $users;
        }

        throw new SerializerException('Unsupported value for ' . self::class . '::' . __FUNCTION__);
    }
}
