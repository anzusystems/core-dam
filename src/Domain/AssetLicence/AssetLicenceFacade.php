<?php

declare(strict_types=1);

namespace App\Domain\AssetLicence;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;

final readonly class AssetLicenceFacade
{
    public function __construct(
        private EntityValidator $validator,
        private AssetLicenceManager $manager,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function create(UpsertAssertLicenceDto $upsertAssertLicenceDto): AssetLicence
    {
        $this->validator->validateDto($upsertAssertLicenceDto);

        return $this->manager->createByDto($upsertAssertLicenceDto);
    }

    /**
     * @throws ValidationException
     */
    public function update(AssetLicence $licence, UpsertAssertLicenceDto $upsertAssertLicenceDto): AssetLicence
    {
        $this->validator->validateDto($upsertAssertLicenceDto);

        return $this->manager->updateByDto($licence, $upsertAssertLicenceDto);
    }
}
