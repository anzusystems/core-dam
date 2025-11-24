<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Validator\Constraints as AppAssert;

#[AppAssert\UserSelectedLicence]
final class UpdateCurrentUserDto
{
    #[Serialize(handler: EntityIdHandler::class)]
    private ?AssetLicence $selectedLicence;

    public function getSelectedLicence(): ?AssetLicence
    {
        return $this->selectedLicence;
    }

    public function setSelectedLicence(?AssetLicence $selectedLicence): self
    {
        $this->selectedLicence = $selectedLicence;

        return $this;
    }
}
