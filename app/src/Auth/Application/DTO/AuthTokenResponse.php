<?php

declare(strict_types=1);

namespace App\Auth\Application\DTO;

final readonly class AuthTokenResponse
{
    public function __construct(
        public string $token,
        public int $expiresIn,
        public ?string $refreshToken = null,
    ) {
    }
}
