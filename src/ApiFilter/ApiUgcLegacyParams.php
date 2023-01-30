<?php

declare(strict_types=1);

namespace App\ApiFilter;

use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

final class ApiUgcLegacyParams
{
    public const ALLOWED_LIMIT = 25;

    private const LIMIT = 'limit';
    private const OFFSET = 'offset';
    private const CREATED_AT_FROM = 'createdAtFrom';
    private const CREATED_AT_UNTIL = 'createdAtUntil';
    private const TEXT = 'text';
    private const FILTER_IN = 'filter_in';
    private const ID = 'id';

    private const DEFAULTS = [
        self::LIMIT => self::ALLOWED_LIMIT,
        self::OFFSET => 0,
        self::CREATED_AT_FROM => null,
        self::CREATED_AT_UNTIL => null,
        self::TEXT => '',
        self::ID => [],
    ];

    private string $text;
    private int $limit;
    private int $offset;
    private array $ids;
    private ?DateTimeImmutable $createdAtFrom;
    private ?DateTimeImmutable $createdAtUntil;

    public function __construct()
    {
        $this->text = self::DEFAULTS[self::TEXT];
        $this->limit = self::DEFAULTS[self::LIMIT];
        $this->offset = self::DEFAULTS[self::OFFSET];
        $this->createdAtFrom = self::DEFAULTS[self::CREATED_AT_FROM];
        $this->createdAtUntil = self::DEFAULTS[self::CREATED_AT_UNTIL];
        $this->ids = self::DEFAULTS[self::ID];
    }

    public function setFromRequest(Request $request): self
    {
        $this->text = (string) $request->query->get(self::TEXT, self::DEFAULTS[self::TEXT]);
        $this->offset = $request->query->getInt(self::OFFSET, self::DEFAULTS[self::OFFSET]);
        $this->limit = $request->query->getInt(self::LIMIT, self::DEFAULTS[self::LIMIT]);
        if (self::ALLOWED_LIMIT !== $this->limit) {
            $this->limit = self::ALLOWED_LIMIT;
        }
        $filterInIds = $request->query->all(self::FILTER_IN)[self::ID] ?? '';
        if ($filterInIds) {
            $this->ids = explode(',', $filterInIds);
        }

        try {
            $createdAtFrom = $request->query->get(self::CREATED_AT_FROM, self::DEFAULTS[self::CREATED_AT_FROM]);
            if ($createdAtFrom) {
                $this->createdAtFrom = new DateTimeImmutable($createdAtFrom);
            }
            $createdAtUntil = $request->query->get(self::CREATED_AT_UNTIL, self::DEFAULTS[self::CREATED_AT_UNTIL]);
            if ($createdAtUntil) {
                $this->createdAtUntil = new DateTimeImmutable($createdAtUntil);
            }
        } catch (Exception) {
            throw new InvalidParameterException('Invalid createdAt.');
        }

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getCreatedAtFrom(): ?DateTimeImmutable
    {
        return $this->createdAtFrom;
    }

    public function getCreatedAtUntil(): ?DateTimeImmutable
    {
        return $this->createdAtUntil;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
