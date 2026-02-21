<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

final class HashedPassword implements \Stringable
{
    private function __construct(private readonly string $hash)
    {
    }

    public static function fromHash(string $hash): self
    {
        if ('' === $hash) {
            throw new \InvalidArgumentException('Password hash cannot be empty.');
        }

        return new self($hash);
    }

    public function value(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
