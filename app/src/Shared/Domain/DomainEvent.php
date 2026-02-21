<?php

declare(strict_types=1);

namespace App\Shared\Domain;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;

    public function eventName(): string;
}
