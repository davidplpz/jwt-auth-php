<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

abstract class UuidValueObject implements \Stringable
{
    public function __construct(private readonly string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException(
                sprintf('<%s> does not allow the value <%s>. Expected a valid UUID.', static::class, $value)
            );
        }
    }

    public static function generate(): static
    {
        return new static(Uuid::v4()->toRfc4122());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
