<?php

declare(strict_types=1);

namespace App\Entity\Embeds;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ArtemisAudioAttributes
{
    #[ORM\Column(type: Types::INTEGER)]
    #[Serialize]
    private int $duration;

    #[ORM\Column(type: Types::INTEGER)]
    #[Serialize]
    private int $premiumDuration;

    public function __construct()
    {
        $this->setDuration(0);
        $this->setPremiumDuration(0);
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPremiumDuration(): int
    {
        return $this->premiumDuration;
    }

    public function setPremiumDuration(int $premiumDuration): self
    {
        $this->premiumDuration = $premiumDuration;

        return $this;
    }
}
