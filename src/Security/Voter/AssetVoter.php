<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use App\Entity\User;
use App\Security\Permission\DamPermissions;

final class AssetVoter extends AbstractVoter
{
    protected function resolveAllow(string $attribute, ?object $subject, User $user): bool
    {
        if (null === $subject) {
            return true;
        }
        $assetLicence = null;
        if ($subject instanceof AssetLicence) {
            $assetLicence = $subject;
        }
        if ($subject instanceof Asset) {
            $assetLicence = $subject->getLicence();
        }

        return $user->getAssetLicences()->containsKey((string) $assetLicence->getId())
            || $user->getAdminToExtSystems()->containsKey((int) $assetLicence->getExtSystem()->getId())
        ;
    }

    protected function getSupportedPermissions(): array
    {
        return [
            DamPermissions::DAM_ASSET_CREATE,
            DamPermissions::DAM_ASSET_UPDATE,
            DamPermissions::DAM_ASSET_VIEW,
            DamPermissions::DAM_VIDEO_CREATE,
            DamPermissions::DAM_VIDEO_UPDATE,
            DamPermissions::DAM_VIDEO_VIEW,
            DamPermissions::DAM_AUDIO_CREATE,
            DamPermissions::DAM_AUDIO_UPDATE,
            DamPermissions::DAM_AUDIO_VIEW,
            DamPermissions::DAM_DOCUMENT_CREATE,
            DamPermissions::DAM_DOCUMENT_UPDATE,
            DamPermissions::DAM_DOCUMENT_VIEW,
            DamPermissions::DAM_IMAGE_CREATE,
            DamPermissions::DAM_IMAGE_UPDATE,
            DamPermissions::DAM_IMAGE_VIEW,
        ];
    }
}
