<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidCredentialsException extends \DomainException
{
    public static function create(): self
    {
        return new self('Invalid credentials.');
    }
}
