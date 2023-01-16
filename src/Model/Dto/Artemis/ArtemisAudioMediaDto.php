<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Enum\ArtemisMediaType;

class ArtemisAudioMediaDto extends ArtemisMediaDto
{
    #[Serialize]
    private string $anzuMediaId = '';

    #[Serialize]
    private ArtemisMediaChannel $mediaChannel;

    #[Serialize]
    private string $directSourceUrl = '';

    #[Serialize]
    private string $premiumDirectSourceUrl = '';


    #[Serialize]
    private int $mediaServiceId = 14;

    public function __construct()
    {
        $this->setType(ArtemisMediaType::Audio);
        $this->setMediaChannel(new ArtemisMediaChannel());
        parent::__construct();
    }

    public function getMediaServiceId(): int
    {
        return $this->mediaServiceId;
    }

    public function setMediaServiceId(int $mediaServiceId): self
    {
        $this->mediaServiceId = $mediaServiceId;
        return $this;
    }

    public function getDirectSourceUrl(): string
    {
        return $this->directSourceUrl;
    }

    public function setDirectSourceUrl(string $directSourceUrl): self
    {
        $this->directSourceUrl = $directSourceUrl;
        return $this;
    }

    public function getPremiumDirectSourceUrl(): string
    {
        return $this->premiumDirectSourceUrl;
    }

    public function setPremiumDirectSourceUrl(string $premiumDirectSourceUrl): self
    {
        $this->premiumDirectSourceUrl = $premiumDirectSourceUrl;
        return $this;
    }

    public function getAnzuMediaId(): string
    {
        return $this->anzuMediaId;
    }

    public function setAnzuMediaId(string $anzuMediaId): self
    {
        $this->anzuMediaId = $anzuMediaId;
        return $this;
    }

    public function getMediaChannel(): ArtemisMediaChannel
    {
        return $this->mediaChannel;
    }

    public function setMediaChannel(ArtemisMediaChannel $mediaChannel): self
    {
        $this->mediaChannel = $mediaChannel;
        return $this;
    }
}
