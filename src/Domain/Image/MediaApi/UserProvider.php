<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use App\Domain\User\UserManager;
use App\Entity\User;
use App\Repository\UserRepository;

final readonly class UserProvider
{
    public function __construct(
        private OAuth2HttpClient $OAuth2HttpClient,
        private UserManager $manager,
        private UserRepository $repository,
    ) {
    }

    public function getUser(int $userId): User
    {
        $user = $this->repository->find($userId);
        if ($user) {
            return $user;
        }

        /** @var User $user */
        $user = $this->manager->createAnzuUser(
            user: (new User()),
            userDto: (new UserDto())
                ->setId($userId)
                ->setEmail($this->getEmail($userId)),
            flush: false
        );

        return $user;
    }

    private function getEmail(int $userId): string
    {
        try {
            return $this->OAuth2HttpClient->getSsoUserInfo((string) $userId)->getEmail();
        } catch (UnsuccessfulAccessTokenRequestException | UnsuccessfulUserInfoRequestException) {
            return 'dam-' . $userId . '@anzusystems.dev';
        }
    }
}
