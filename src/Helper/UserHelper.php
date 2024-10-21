<?php

declare(strict_types=1);

namespace App\Helper;

use AnzuSystems\CommonBundle\Helper\CollectionHelper;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

final class UserHelper
{
    /**
     * @return Collection<int, AssetLicence>
     */
    public static function getAllUserLicences(User $user): Collection
    {
        $licences = [];

        foreach ($user->getAssetLicences() as $licence) {
            $licences[(int) $licence->getId()] = $licence;
        }
        foreach ($user->getLicenceGroups() as $licenceGroup) {
            foreach ($licenceGroup->getLicences() as $licence) {
                $licences[(int) $licence->getId()] = $licence;
            }
        }

        return CollectionHelper::newCollection(array_values($licences));
    }
}
