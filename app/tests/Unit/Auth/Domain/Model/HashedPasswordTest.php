<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Domain\Model;

use App\Auth\Domain\Model\HashedPassword;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    public function testCreatesFromHashString(): void
    {
        $hash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';
        $password = HashedPassword::fromHash($hash);

        $this->assertSame($hash, $password->value());
    }

    public function testRejectsEmptyHash(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        HashedPassword::fromHash('');
    }

    public function testCastsToString(): void
    {
        $hash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';
        $password = HashedPassword::fromHash($hash);

        $this->assertSame($hash, (string) $password);
    }
}
