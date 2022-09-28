<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\Contracts\Exception\AnzuException;
use App\Entity\User;
use App\Permission\Grants;
use App\Permission\UserPermissionResolver;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

abstract class AbstractVoter extends Voter
{
    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, $this->getSupportedPermissions(), true);
    }

    /**
     * @throws AnzuException
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // If role admin, grant access
        if ($this->security->isGranted(AnzuUser::ROLE_ADMIN)) {
            return true;
        }

        $userPermissions = UserPermissionResolver::resolve($user);
        $userPermissionGrant = $userPermissions[$attribute];

        return match ($userPermissionGrant) {
            Grants::GRANT_DENY => false,
            Grants::GRANT_ALLOW => $this->resolveAllow($attribute, $subject, $user),
            Grants::GRANT_ALLOW_OWNER => $this->resolveAllowOwner($attribute, $subject, $user),
            default => throw new AnzuException('User permission could not be resolved!'),
        };
    }

    protected function resolveAllow(string $attribute, ?object $subject, User $user): bool
    {
        return true;
    }

    protected function resolveAllowOwner(string $attribute, object $subject, User $user): bool
    {
        throw new RuntimeException(sprintf('Please create voter for %s and %s!', $attribute, $subject::class));
    }

    abstract protected function getSupportedPermissions(): array;
}
