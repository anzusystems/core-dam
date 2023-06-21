<?php

declare(strict_types=1);

namespace App\Model\Dto\Asset;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class RtmpAssetDto
{
    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Assert\Length(max: 192, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[AppAssert\RtmpPath]
    private string $path = '';

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
