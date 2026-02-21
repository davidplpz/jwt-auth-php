<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Shared\Domain\DomainEvent;

final class UserAuthenticated implements DomainEvent
{
    private readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly string $userId,
        private readonly string $email,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return 'auth.user_authenticated';
    }
}
