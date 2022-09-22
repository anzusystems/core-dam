<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Token\RefreshToken;

use AnzuSystems\AuthBundle\Model\RefreshTokenDto;

interface RefreshTokenStorageInterface
{
    public function fetch(int $userId, string $tokenId): RefreshTokenDto;

    public function store(int $userId, RefreshTokenDto $refreshTokenDto);

    /**
     * Needs to remove from the storage user's refresh token data by device hash.
     */
    public function remove(int $userId, string $tokenId): void;
}
