<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\Embeds\ArtemisAudioFlags;
use App\Entity\Embeds\ArtemisAudioTexts;
use App\Repository\ArtemisAudioDistributionRepository;
use App\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtemisAudioDistributionRepository::class)]
#[AppAssert\ArtemisAudioConstraint]
class ArtemisAudioDistribution extends Distribution
{
    #[ORM\Embedded(ArtemisAudioTexts::class)]
    #[Assert\Valid]
    #[Serialize]
    private ArtemisAudioTexts $texts;

    #[ORM\Embedded(ArtemisAudioFlags::class)]
    #[Assert\Valid]
    #[Serialize]
    private ArtemisAudioFlags $flags;

    public function __construct()
    {
        parent::__construct();
        $this->setTexts(new ArtemisAudioTexts());
        $this->setFlags(new ArtemisAudioFlags());
    }

    public function getTexts(): ArtemisAudioTexts
    {
        return $this->texts;
    }

    public function setTexts(ArtemisAudioTexts $texts): self
    {
        $this->texts = $texts;

        return $this;
    }

    public function getFlags(): ArtemisAudioFlags
    {
        return $this->flags;
    }

    public function setFlags(ArtemisAudioFlags $flags): self
    {
        $this->flags = $flags;

        return $this;
    }
}
