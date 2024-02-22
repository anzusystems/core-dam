<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\Embeds\ArtemisVideoFlags;
use App\Entity\Embeds\ArtemisVideoTexts;
use App\Repository\ArtemisVideoDistributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtemisVideoDistributionRepository::class)]
class ArtemisVideoDistribution extends Distribution
{
    private const string DISCRIMINATOR = 'customDistribution';

    #[ORM\Embedded(ArtemisVideoTexts::class)]
    #[Assert\Valid]
    #[Serialize]
    private ArtemisVideoTexts $texts;

    #[ORM\Embedded(ArtemisVideoFlags::class)]
    #[Assert\Valid]
    #[Serialize]
    private ArtemisVideoFlags $flags;

    public function __construct()
    {
        parent::__construct();
        $this->setTexts(new ArtemisVideoTexts());
        $this->setFlags(new ArtemisVideoFlags());
    }

    public function getTexts(): ArtemisVideoTexts
    {
        return $this->texts;
    }

    public function setTexts(ArtemisVideoTexts $texts): self
    {
        $this->texts = $texts;

        return $this;
    }

    public function getFlags(): ArtemisVideoFlags
    {
        return $this->flags;
    }

    public function setFlags(ArtemisVideoFlags $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function getDiscriminator(): string
    {
        return self::DISCRIMINATOR;
    }
}
