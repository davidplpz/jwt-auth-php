<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Domain\Model;

use App\Auth\Domain\Event\UserRegistered;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const EMAIL = 'user@example.com';
    private const HASH = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';

    public function testRegistersNewUser(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );

        $this->assertSame(self::UUID, $user->id()->value());
        $this->assertSame(self::EMAIL, $user->email()->value());
        $this->assertSame(self::HASH, $user->password()->value());
    }

    public function testRegisterEmitsUserRegisteredEvent(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );

        $events = $user->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserRegistered::class, $events[0]);
    }

    public function testUserRegisteredEventContainsCorrectData(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );

        /** @var UserRegistered $event */
        $event = $user->pullDomainEvents()[0];

        $this->assertSame(self::UUID, $event->userId());
        $this->assertSame(self::EMAIL, $event->email());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->occurredOn());
    }

    public function testPullDomainEventsClearsEvents(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );

        $user->pullDomainEvents();

        $this->assertEmpty($user->pullDomainEvents());
    }

    public function testIdReturnsUserIdValueObject(): void
    {
        $id = new UserId(self::UUID);
        $user = User::register($id, new Email(self::EMAIL), HashedPassword::fromHash(self::HASH));

        $this->assertTrue($id->equals($user->id()));
    }

    public function testEmailReturnsEmailValueObject(): void
    {
        $email = new Email(self::EMAIL);
        $user = User::register(new UserId(self::UUID), $email, HashedPassword::fromHash(self::HASH));

        $this->assertTrue($email->equals($user->email()));
    }
}
