<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

use App\Auth\Domain\Exception\WeakPasswordException;

final class PlainPassword implements \Stringable
{
    private const MIN_LENGTH = 8;

    private readonly string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (mb_strlen($value) < self::MIN_LENGTH) {
            throw WeakPasswordException::withReason(
                sprintf('must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if (!preg_match('/[A-Z]/', $value)) {
            throw WeakPasswordException::withReason('must contain at least one uppercase letter');
        }

        if (!preg_match('/[a-z]/', $value)) {
            throw WeakPasswordException::withReason('must contain at least one lowercase letter');
        }

        if (!preg_match('/[0-9]/', $value)) {
            throw WeakPasswordException::withReason('must contain at least one digit');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            throw WeakPasswordException::withReason('must contain at least one special character');
        }
    }
}
