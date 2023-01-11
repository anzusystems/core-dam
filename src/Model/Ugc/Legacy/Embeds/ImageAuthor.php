<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageAuthor
{
    #[Serialize]
    private string $customAuthor = '';

    public static function getInstance(string $name): self
    {
        return (new self())
            ->setCustomAuthor($name)
        ;
    }

    public function getCustomAuthor(): string
    {
        return $this->customAuthor;
    }

    public function setCustomAuthor(string $customAuthor): self
    {
        $this->customAuthor = $customAuthor;

        return $this;
    }
}
