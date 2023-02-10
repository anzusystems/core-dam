<?php

declare(strict_types=1);

namespace App\Domain\AssetLicence;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Traits\ValidatorAwareTrait;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;

final class AssetLicenceFacade
{
    use ValidatorAwareTrait;

    public function __construct(
        private readonly AssetLicenceManager $manager,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function create(UpsertAssertLicenceDto $upsertAssertLicenceDto): AssetLicence
    {
        $this->validator->validate($upsertAssertLicenceDto);

        return $this->manager->createByDto($upsertAssertLicenceDto);
    }

    /**
     * @throws ValidationException
     */
    public function update(AssetLicence $licence, UpsertAssertLicenceDto $upsertAssertLicenceDto): AssetLicence
    {
        $this->validator->validate($upsertAssertLicenceDto);

        return $this->manager->updateByDto($licence, $upsertAssertLicenceDto);
    }
}
