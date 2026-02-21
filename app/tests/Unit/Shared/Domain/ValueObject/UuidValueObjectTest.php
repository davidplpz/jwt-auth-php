<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\UuidValueObject;
use PHPUnit\Framework\TestCase;

final class UuidValueObjectTest extends TestCase
{
    private const VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const ANOTHER_UUID = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

    public function testCreatesFromValidUuid(): void
    {
        $vo = new class(self::VALID_UUID) extends UuidValueObject {};

        $this->assertSame(self::VALID_UUID, $vo->value());
    }

    public function testGeneratesValidUuid(): void
    {
        $vo = ConcreteUuid::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $vo->value()
        );
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new class('not-a-uuid') extends UuidValueObject {};
    }

    public function testRejectsEmptyUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new class('') extends UuidValueObject {};
    }

    public function testTwoObjectsWithSameUuidAreEqual(): void
    {
        $a = new class(self::VALID_UUID) extends UuidValueObject {};
        $b = new class(self::VALID_UUID) extends UuidValueObject {};

        $this->assertTrue($a->equals($b));
    }

    public function testTwoObjectsWithDifferentUuidAreNotEqual(): void
    {
        $a = new class(self::VALID_UUID) extends UuidValueObject {};
        $b = new class(self::ANOTHER_UUID) extends UuidValueObject {};

        $this->assertFalse($a->equals($b));
    }

    public function testCastsToString(): void
    {
        $vo = new class(self::VALID_UUID) extends UuidValueObject {};

        $this->assertSame(self::VALID_UUID, (string) $vo);
    }
}

final class ConcreteUuid extends UuidValueObject
{
}
