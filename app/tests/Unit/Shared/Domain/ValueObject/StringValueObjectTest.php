<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;
use PHPUnit\Framework\TestCase;

final class StringValueObjectTest extends TestCase
{
    public function testStoresValueCorrectly(): void
    {
        $vo = new class('hello') extends StringValueObject {};

        $this->assertSame('hello', $vo->value());
    }

    public function testTwoObjectsWithSameValueAreEqual(): void
    {
        $a = new class('same') extends StringValueObject {};
        $b = new class('same') extends StringValueObject {};

        $this->assertTrue($a->equals($b));
    }

    public function testTwoObjectsWithDifferentValueAreNotEqual(): void
    {
        $a = new class('one') extends StringValueObject {};
        $b = new class('two') extends StringValueObject {};

        $this->assertFalse($a->equals($b));
    }

    public function testCastsToString(): void
    {
        $vo = new class('cast') extends StringValueObject {};

        $this->assertSame('cast', (string) $vo);
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new class('') extends StringValueObject {};
    }
}
