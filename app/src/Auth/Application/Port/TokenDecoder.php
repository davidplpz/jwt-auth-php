<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

interface TokenDecoder
{
    /**
     * @return array{user_id: string, email: string}
     *
     * @throws \App\Auth\Domain\Exception\InvalidCredentialsException
     */
    public function decode(string $token): array;
}
