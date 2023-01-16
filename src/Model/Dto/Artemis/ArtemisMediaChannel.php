<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisMediaChannel
{
    #[Serialize]
    private string $anzuId = '';

    public function getAnzuId(): string
    {
        return $this->anzuId;
    }

    public function setAnzuId(string $anzuId): self
    {
        $this->anzuId = $anzuId;
        return $this;
    }
}
