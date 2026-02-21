<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class JwtUser implements UserInterface
{
    public function __construct(
        private string $userId,
        private string $email,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }

    public function email(): string
    {
        return $this->email;
    }

    /** @return string[] */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }
}
