<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\Embeds\ArtemisAudioTexts;
use App\Repository\ArtemisAudioDistributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtemisAudioDistributionRepository::class)]
class ArtemisAudioDistribution extends Distribution
{
    #[ORM\Embedded(ArtemisAudioTexts::class)]
    #[Assert\Valid]
    #[Serialize]
    protected ArtemisAudioTexts $texts;

    public function __construct()
    {
        parent::__construct();
        $this->setTexts(new ArtemisAudioTexts());
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
}
