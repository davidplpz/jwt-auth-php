<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Domain\Model;

use App\Auth\Domain\Exception\WeakPasswordException;
use App\Auth\Domain\Model\PlainPassword;
use PHPUnit\Framework\TestCase;

final class PlainPasswordTest extends TestCase
{
    public function testCreatesFromValidPassword(): void
    {
        $password = new PlainPassword('Str0ng!Pass');

        $this->assertSame('Str0ng!Pass', $password->value());
    }

    public function testRejectsTooShortPassword(): void
    {
        $this->expectException(WeakPasswordException::class);

        new PlainPassword('Sh0r!');
    }

    public function testRejectsPasswordWithoutUppercase(): void
    {
        $this->expectException(WeakPasswordException::class);

        new PlainPassword('nouppercase1!');
    }

    public function testRejectsPasswordWithoutLowercase(): void
    {
        $this->expectException(WeakPasswordException::class);

        new PlainPassword('NOLOWERCASE1!');
    }

    public function testRejectsPasswordWithoutDigit(): void
    {
        $this->expectException(WeakPasswordException::class);

        new PlainPassword('NoDigitHere!');
    }

    public function testRejectsPasswordWithoutSpecialCharacter(): void
    {
        $this->expectException(WeakPasswordException::class);

        new PlainPassword('NoSpecial1a');
    }

    public function testCastsToString(): void
    {
        $password = new PlainPassword('Str0ng!Pass');

        $this->assertSame('Str0ng!Pass', (string) $password);
    }
}
