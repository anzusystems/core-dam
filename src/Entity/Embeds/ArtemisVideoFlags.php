<?php

declare(strict_types=1);

namespace App\Entity\Embeds;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ArtemisVideoFlags
{
    #[ORM\Column(type: Types::BOOLEAN)]
    #[Serialize]
    private bool $createArticle;

    public function __construct()
    {
        $this->setCreateArticle(false);
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
}
