<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class WeakPasswordException extends \InvalidArgumentException
{
    public static function withReason(string $reason): self
    {
        return new self(sprintf('Password is too weak: %s.', $reason));
    }
}
