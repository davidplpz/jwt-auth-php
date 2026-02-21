<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract class StringValueObject implements \Stringable
{
    public function __construct(private readonly string $value)
    {
        if ('' === $value) {
            throw new \InvalidArgumentException(
                sprintf('<%s> does not allow an empty value.', static::class)
            );
        }
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
