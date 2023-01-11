<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\CoreDamBundle\Entity\Interfaces\AssetLicenceInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UgcVoter extends Voter
{
    // UGC access
    public const DAM_UGC_ACCESS = 'dam_ugc_access';

    protected function supports(string $attribute, $subject): bool
    {
        return self::DAM_UGC_ACCESS === $attribute && $subject instanceof AssetLicenceInterface;
    }

    /**
     * @param AssetLicenceInterface $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        if ($user->getAdminToExtSystems()->containsKey((int) $subject->getLicence()->getExtSystem()->getId())) {
            return true;
        }

        return $user->getAssetLicences()->containsKey((string) $subject->getLicence()->getId());
    }
}
