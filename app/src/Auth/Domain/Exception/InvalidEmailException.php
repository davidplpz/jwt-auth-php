<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidEmailException extends \InvalidArgumentException
{
    public static function withValue(string $email): self
    {
        return new self(sprintf('The email <%s> is not valid.', $email));
    }
}
