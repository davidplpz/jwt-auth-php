<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Infrastructure\Security;

use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\PlainPassword;
use App\Auth\Infrastructure\Security\SymfonyPasswordHasher;
use PHPUnit\Framework\TestCase;

final class SymfonyPasswordHasherTest extends TestCase
{
    private SymfonyPasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new SymfonyPasswordHasher();
    }

    public function testHashReturnsHashedPassword(): void
    {
        $plain = new PlainPassword('Str0ng!Pass');
        $hashed = $this->hasher->hash($plain);

        $this->assertInstanceOf(HashedPassword::class, $hashed);
        $this->assertNotSame('Str0ng!Pass', $hashed->value());
    }

    public function testVerifyReturnsTrueForMatchingPassword(): void
    {
        $plain = new PlainPassword('Str0ng!Pass');
        $hashed = $this->hasher->hash($plain);

        $this->assertTrue($this->hasher->verify($plain, $hashed));
    }

    public function testVerifyReturnsFalseForWrongPassword(): void
    {
        $plain = new PlainPassword('Str0ng!Pass');
        $wrong = new PlainPassword('Wr0ng!Pass');
        $hashed = $this->hasher->hash($plain);

        $this->assertFalse($this->hasher->verify($wrong, $hashed));
    }

    public function testHashProducesDifferentHashesForSamePassword(): void
    {
        $plain = new PlainPassword('Str0ng!Pass');

        $hash1 = $this->hasher->hash($plain);
        $hash2 = $this->hasher->hash($plain);

        $this->assertNotSame($hash1->value(), $hash2->value());
    }
}
