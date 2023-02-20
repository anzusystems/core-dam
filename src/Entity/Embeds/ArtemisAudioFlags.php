<?php

declare(strict_types=1);

namespace App\Entity\Embeds;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class ArtemisAudioFlags
{
    #[ORM\Column(type: Types::BOOLEAN)]
    #[Serialize]
    private bool $createArticle;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Serialize]
    private bool $bonusEpisode;

    public function __construct()
    {
        $this->setCreateArticle(false);
        $this->setBonusEpisode(false);
    }

    public function isCreateArticle(): bool
    {
        return $this->createArticle;
    }

    public function setCreateArticle(bool $createArticle): self
    {
        $this->createArticle = $createArticle;

        return $this;
    }

    public function isBonusEpisode(): bool
    {
        return $this->bonusEpisode;
    }

    public function setBonusEpisode(bool $bonusEpisode): self
    {
        $this->bonusEpisode = $bonusEpisode;

        return $this;
    }
}
