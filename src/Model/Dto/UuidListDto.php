<?php

declare(strict_types=1);

namespace App\Model\Dto;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\ArrayStringHandler;
use Symfony\Component\Validator\Constraints as Assert;

final class UuidListDto
{
    public const int MAX_IDS = 100;

    #[Assert\Count(
        min: 1,
        max: self::MAX_IDS,
        minMessage: ValidationException::ERROR_FIELD_LENGTH_MIN,
        maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX
    )]
    #[Assert\All(
        constraints: [
            new Assert\Uuid(),
        ]
    )]
    #[Serialize(handler: ArrayStringHandler::class)]
    private array $ids = [];

    public function getIds(): array
    {
        return $this->ids;
    }

    public function setIds(array $ids): self
    {
        $this->ids = $ids;

        return $this;
    }
}
