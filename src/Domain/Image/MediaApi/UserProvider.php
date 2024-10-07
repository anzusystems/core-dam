<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use App\Domain\User\DeprecatedUserManager;
use App\Entity\User;
use App\Repository\UserRepository;

final readonly class UserProvider
{
    public function __construct(
        private OAuth2HttpClient $OAuth2HttpClient,
        private DeprecatedUserManager $manager,
        private UserRepository $repository,
    ) {
    }

    public function getUser(int $userId): User
    {
        $user = $this->repository->find($userId);
        if ($user) {
            return $user;
        }
        $email = $this->getEmail($userId);
        $userDto = (new UserDto())
            ->setId($userId)
            ->setEmail($email);
        $this->setupPerson($userDto);

        /** @var User $user */
        $user = $this->manager->createAnzuUser(
            user: (new User()),
            userDto: $userDto,
            flush: false
        );

        return $user;
    }

    public function setupPerson(UserDto $userDto): void
    {
        $namePart = explode('@', $userDto->getEmail())[0] ?? null;
        if (null === $namePart) {
            return;
        }

        $nameParts = explode('.', $namePart);

        $firstName = isset($nameParts[0]) ? ucfirst(trim($nameParts[0])) : '';
        $lastName = isset($nameParts[1]) ? ucfirst(trim($nameParts[1])) : null;

        if (null === $lastName) {
            $userDto->getAvatar()
                ->setText(str_repeat(StringHelper::getFirstChar($firstName), 2));

            $userDto->getPerson()
                ->setLastName($firstName)
                ->setFullName($firstName)
            ;

            return;
        }

        $userDto->getAvatar()
            ->setText(StringHelper::getFirstChar($firstName) . StringHelper::getFirstChar($lastName));

        $userDto->getPerson()
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setFullName(trim($firstName . ' ' . $lastName))
        ;
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
