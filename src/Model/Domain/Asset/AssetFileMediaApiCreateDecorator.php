<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use App\Exception\ValidationException;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class AssetFileMediaApiCreateDecorator extends AssetFileMediaApiDecorator
{
    #[Serialize]
    protected DateTimeImmutable $createdAt;

    #[Serialize]
    #[Assert\Range(minMessage: ValidationException::ERROR_FIELD_RANGE_MIN, min: 1)]
    protected int $idCentralUserCreated = 0;

    #[Serialize]
    #[Assert\Range(minMessage: ValidationException::ERROR_FIELD_RANGE_MIN, min: 1)]
    protected int $idStock = 0;

    public function __construct()
    {
        parent::__construct();
        $this->setCreatedAt(App::getMinDate());
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getIdCentralUserCreated(): int
    {
        return $this->idCentralUserCreated;
    }

    public function setIdCentralUserCreated(int $idCentralUserCreated): self
    {
        $this->idCentralUserCreated = $idCentralUserCreated;
        return $this;
    }

    public function getIdStock(): int
    {
        return $this->idStock;
    }

    public function setIdStock(int $idStock): self
    {
        $this->idStock = $idStock;
        return $this;
    }
}
