<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Domain\Model;

use App\Auth\Domain\Exception\InvalidEmailException;
use App\Auth\Domain\Model\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testCreatesFromValidEmail(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    public function testNormalizesToLowercase(): void
    {
        $email = new Email('User@EXAMPLE.COM');

        $this->assertSame('user@example.com', $email->value());
    }

    public function testTwoEmailsWithSameValueAreEqual(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('USER@example.com');

        $this->assertTrue($a->equals($b));
    }

    public function testTwoEmailsWithDifferentValueAreNotEqual(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('other@example.com');

        $this->assertFalse($a->equals($b));
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email('not-an-email');
    }

    public function testRejectsEmptyEmail(): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email('');
    }

    public function testRejectsEmailWithoutDomain(): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email('user@');
    }

    public function testCastsToString(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', (string) $email);
    }
}
