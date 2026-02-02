<?php

declare(strict_types=1);

namespace App\Model\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class ApiPubParams
{
    public const int LIMIT_DEFAULT = 20;
    public const int MAX_PAGE = 100;

    public const int MAX_EXCLUDED_IDS = 100;

    /**
     * @var list<int>
     */
    public const array ALLOWED_LIMITS = [1, 10, 20, 50]; // 1 is for path '/{slug}/podcasts/{podcastId}/podcast-episodes'

    private const string PAGE = 'page';
    private const string EXCLUDE_IDS = 'excludeIds';
    private const string LIMIT = 'limit';

    private const array DEFAULTS = [
        self::PAGE => 1,
        self::EXCLUDE_IDS => [],
        self::LIMIT => self::LIMIT_DEFAULT,
    ];

    public function __construct(
        private int $page = self::DEFAULTS[self::PAGE],
        private array $excludeIds = self::DEFAULTS[self::EXCLUDE_IDS],
        private int $limit = self::DEFAULTS[self::LIMIT],
    ) {
    }

    public static function createFromRequest(Request $request): self
    {
        $page = $request->query->getInt(self::PAGE, self::DEFAULTS[self::PAGE]);
        if ($page < 1 || $page > self::MAX_PAGE) {
            throw new BadRequestHttpException('page_value_not_allowed');
        }

        $limit = $request->query->getInt(self::LIMIT, self::DEFAULTS[self::LIMIT]);
        if (false === in_array($limit, self::ALLOWED_LIMITS, true)) {
            throw new BadRequestHttpException('limit_value_not_allowed');
        }

        /** @var array $excludeIds */
        $excludeIds = $request->query->all(self::EXCLUDE_IDS) ?: self::DEFAULTS[self::EXCLUDE_IDS];
        if (count($excludeIds) > self::MAX_EXCLUDED_IDS) {
            throw new BadRequestHttpException('max_excluded_ids_exceeded');
        }

        return new self(
            page: $page,
            excludeIds: $excludeIds,
            limit: $limit,
        );
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getExcludeIds(): array
    {
        return $this->excludeIds;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
