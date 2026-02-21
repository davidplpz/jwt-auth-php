<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Application\Query;

use App\Auth\Application\DTO\UserProfileResponse;
use App\Auth\Application\Query\GetUserProfile\GetUserProfileQuery;
use App\Auth\Application\Query\GetUserProfile\GetUserProfileQueryHandler;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\UserRepository;
use PHPUnit\Framework\TestCase;

final class GetUserProfileQueryHandlerTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const EMAIL = 'user@example.com';
    private const HASH = '$2y$10$hashedpasswordvalue1234567890abcdefghijklmnopqrst';

    private UserRepository&\PHPUnit\Framework\MockObject\Stub $userRepository;
    private GetUserProfileQueryHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createStub(UserRepository::class);
        $this->handler = new GetUserProfileQueryHandler($this->userRepository);
    }

    public function testReturnsUserProfile(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );
        $user->pullDomainEvents();

        $this->userRepository->method('findById')->willReturn($user);

        $response = ($this->handler)(new GetUserProfileQuery(self::UUID));

        $this->assertInstanceOf(UserProfileResponse::class, $response);
        $this->assertSame(self::UUID, $response->id);
        $this->assertSame(self::EMAIL, $response->email);
    }

    public function testThrowsWhenUserNotFound(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);

        ($this->handler)(new GetUserProfileQuery(self::UUID));
    }
}
