<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\PlainPassword;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasher
{
    private readonly PasswordHasherInterface $hasher;

    public function __construct()
    {
        $factory = new PasswordHasherFactory([
            'default' => ['algorithm' => 'auto'],
        ]);
        $this->hasher = $factory->getPasswordHasher('default');
    }

    public function hash(PlainPassword $password): HashedPassword
    {
        return HashedPassword::fromHash(
            $this->hasher->hash($password->value())
        );
    }

    public function verify(PlainPassword $plain, HashedPassword $hashed): bool
    {
        return $this->hasher->verify($hashed->value(), $plain->value());
    }
}
