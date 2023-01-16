<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageTextsUpdateDto
{
    #[Serialize]
    #[Assert\Length(max: 5_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    private string $description;

    public function __construct()
    {
        $this->setDescription('');
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
