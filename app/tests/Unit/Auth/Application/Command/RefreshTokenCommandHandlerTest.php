<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Application\Command;

use App\Auth\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Auth\Application\Command\RefreshToken\RefreshTokenCommandHandler;
use App\Auth\Application\DTO\AuthTokenResponse;
use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\RefreshToken;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\RefreshTokenRepository;
use App\Auth\Domain\Port\UserRepository;
use PHPUnit\Framework\TestCase;

final class RefreshTokenCommandHandlerTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const EMAIL = 'user@example.com';
    private const HASH = '$2y$10$hashedvalue';
    private const REFRESH = 'valid-refresh-token-hex';

    private RefreshTokenRepository&\PHPUnit\Framework\MockObject\Stub $refreshTokenRepo;
    private UserRepository&\PHPUnit\Framework\MockObject\Stub $userRepo;
    private TokenGenerator&\PHPUnit\Framework\MockObject\Stub $tokenGenerator;
    private RefreshTokenCommandHandler $handler;

    protected function setUp(): void
    {
        $this->refreshTokenRepo = $this->createStub(RefreshTokenRepository::class);
        $this->userRepo = $this->createStub(UserRepository::class);
        $this->tokenGenerator = $this->createStub(TokenGenerator::class);
        $this->handler = new RefreshTokenCommandHandler(
            $this->refreshTokenRepo,
            $this->userRepo,
            $this->tokenGenerator,
        );
    }

    public function testRefreshesTokenSuccessfully(): void
    {
        $userId = new UserId(self::UUID);
        $refreshToken = RefreshToken::create(self::REFRESH, $userId, new \DateTimeImmutable('+1 day'));
        $user = $this->createUser($userId);

        $this->refreshTokenRepo->method('findByToken')->willReturn($refreshToken);
        $this->userRepo->method('findById')->willReturn($user);
        $this->tokenGenerator->method('generate')->willReturn('new.jwt.token');

        $response = ($this->handler)(new RefreshTokenCommand(self::REFRESH));

        $this->assertInstanceOf(AuthTokenResponse::class, $response);
        $this->assertSame('new.jwt.token', $response->token);
        $this->assertNotNull($response->refreshToken);
        $this->assertNotSame(self::REFRESH, $response->refreshToken);
    }

    public function testThrowsWhenRefreshTokenNotFound(): void
    {
        $this->refreshTokenRepo->method('findByToken')->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new RefreshTokenCommand('nonexistent'));
    }

    public function testThrowsWhenRefreshTokenIsExpired(): void
    {
        $userId = new UserId(self::UUID);
        $expired = RefreshToken::create(self::REFRESH, $userId, new \DateTimeImmutable('-1 day'));

        $this->refreshTokenRepo->method('findByToken')->willReturn($expired);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new RefreshTokenCommand(self::REFRESH));
    }

    public function testThrowsWhenRefreshTokenIsRevoked(): void
    {
        $userId = new UserId(self::UUID);
        $revoked = RefreshToken::create(self::REFRESH, $userId, new \DateTimeImmutable('+1 day'));
        $revoked->revoke();

        $this->refreshTokenRepo->method('findByToken')->willReturn($revoked);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new RefreshTokenCommand(self::REFRESH));
    }

    private function createUser(UserId $id): User
    {
        $user = User::register($id, new Email(self::EMAIL), HashedPassword::fromHash(self::HASH));
        $user->pullDomainEvents();

        return $user;
    }
}
