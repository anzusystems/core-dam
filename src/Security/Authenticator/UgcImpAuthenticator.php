<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Entity\User;
use App\Model\UgcCookieConfiguration;
use App\Repository\UserRepository;
use App\Security\Authenticator\Token\UgcImpAuthenticationToken;
use App\Security\Permission\DamPermissions;
use App\Security\Permission\Grants;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Token\Plain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class UgcImpAuthenticator extends AbstractUgcAuthenticator
{
    private User $originalUser;

    public function __construct(
        private readonly Configuration $jwtUgcImpConfiguration,
        private readonly UgcCookieConfiguration $ugcSecurityConfiguration,
        private readonly UserRepository $userRepo,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->cookies->has($this->ugcSecurityConfiguration->getJwtImpName());
    }

    public function authenticate(Request $request): Passport
    {
        $credentialsToken = $this->getCredentials($request);
        $credentialsChecker = function (Plain $token, User $user): bool {
            return $this->checkCredentials($token, $user);
        };
        $userProvider = function (string $userId) use ($credentialsToken): User {
            return $this->getUser($userId, $credentialsToken);
        };

        return new Passport(
            new UserBadge((string) $credentialsToken->claims()->get('sub'), $userProvider),
            new CustomCredentials($credentialsChecker, $credentialsToken)
        );
    }

    public function createToken(Passport $passport, string $firewallName): UgcImpAuthenticationToken
    {
        return new UgcImpAuthenticationToken(
            $passport->getUser(),
            $firewallName,
            $passport->getUser()->getRoles(),
            $this->originalUser
        );
    }

    private function getUser(string $userIdentifier, Plain $credentials): User
    {
        $originalUserId = $credentials->claims()->get('imp');
        $originalUser = $this->userRepo->findOneBySsoUserId($originalUserId);
        if (null === $originalUser) {
            throw new UserNotFoundException(sprintf('Original user with SSO ID (%s) not found!', $originalUserId));
        }
        $this->originalUser = $originalUser;

        $user = $this->userRepo->findOneBySsoUserId($userIdentifier);
        if (false === ($user instanceof User)) {
            throw new UserNotFoundException(sprintf('User with SSO ID (%s) not found!', $originalUserId));
        }

        return $user;
    }

    private function checkCredentials(Plain $credentials, User $user): bool
    {
        $this->checkToken($this->jwtUgcImpConfiguration, $credentials, $user);

        if (empty($this->originalUser)) {
            throw new AuthenticationException('Original user should be set at this stage!');
        }

        if (false === $user->isEnabled()) {
            throw new AuthenticationException(sprintf('User (%d) is not active or is disabled!', (int) $user->getId()));
        }


        $grantForUgcImp = $this->originalUser->getResolvedPermissions()[DamPermissions::DAM_USER_UGC_IMPERSONATE] ?? null;
        $hasPermission = $this->originalUser->hasRole(User::ROLE_ADMIN) || $grantForUgcImp === Grants::GRANT_ALLOW;
        if ($hasPermission && $this->originalUser->isEnabled()) {
            return true;
        }

        throw new AccessDeniedException(sprintf(
            'Original user (%d) is not disabled or has no permission to impersonate user (%d)!',
            (int) $this->originalUser->getId(),
            (int) $user->getId()
        ));
    }

    private function getCredentials(Request $request): Plain
    {
        $plainImpersonateUserToken = (string) $request->cookies->get(
            $this->ugcSecurityConfiguration->getJwtImpName()
        );

        try {
            return $this->jwtUgcImpConfiguration->parser()->parse($plainImpersonateUserToken);
        } catch (Exception $exception) {
            throw new AccessDeniedException($plainImpersonateUserToken, $exception);
        }
    }
}
