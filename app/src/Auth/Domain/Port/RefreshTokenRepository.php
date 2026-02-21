<?php

declare(strict_types=1);

namespace App\Auth\Domain\Port;

use App\Auth\Domain\Model\RefreshToken;

interface RefreshTokenRepository
{
    public function save(RefreshToken $token): void;

    public function findByToken(string $token): ?RefreshToken;
}
