<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageAuthorDto
{
    #[Serialize]
    #[Assert\Length(max: 255, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
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
