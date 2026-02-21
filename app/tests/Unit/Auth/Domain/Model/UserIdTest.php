<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Domain\Model;

use App\Auth\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';

    public function testCreatesFromValidUuid(): void
    {
        $id = new UserId(self::UUID);

        $this->assertSame(self::UUID, $id->value());
    }

    public function testGeneratesValidUuid(): void
    {
        $id = UserId::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id->value()
        );
    }

    public function testTwoIdsWithSameValueAreEqual(): void
    {
        $this->assertTrue(
            (new UserId(self::UUID))->equals(new UserId(self::UUID))
        );
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserId('invalid');
    }
}
