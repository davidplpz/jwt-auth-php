<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

final class RefreshToken
{
    private function __construct(
        private readonly string $token,
        private readonly UserId $userId,
        private readonly \DateTimeImmutable $expiresAt,
        private bool $revoked = false,
    ) {
    }

    public static function create(string $token, UserId $userId, \DateTimeImmutable $expiresAt): self
    {
        return new self($token, $userId, $expiresAt);
    }

    public function token(): string
    {
        return $this->token;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isRevoked();
    }
}
