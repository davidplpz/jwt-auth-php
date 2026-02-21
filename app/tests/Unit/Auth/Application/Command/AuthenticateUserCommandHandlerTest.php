<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Application\Command;

use App\Auth\Application\Command\AuthenticateUser\AuthenticateUserCommand;
use App\Auth\Application\Command\AuthenticateUser\AuthenticateUserCommandHandler;
use App\Auth\Application\DTO\AuthTokenResponse;
use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\PlainPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\RefreshTokenRepository;
use App\Auth\Domain\Port\UserRepository;
use PHPUnit\Framework\TestCase;

final class AuthenticateUserCommandHandlerTest extends TestCase
{
    private const EMAIL = 'user@example.com';
    private const PASSWORD = 'Str0ng!Pass';
    private const HASH = '$2y$10$hashedpasswordvalue1234567890abcdefghijklmnopqrst';
    private const TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.abc';

    private UserRepository&\PHPUnit\Framework\MockObject\Stub $userRepository;
    private PasswordHasher&\PHPUnit\Framework\MockObject\Stub $passwordHasher;
    private TokenGenerator&\PHPUnit\Framework\MockObject\Stub $tokenGenerator;
    private RefreshTokenRepository&\PHPUnit\Framework\MockObject\Stub $refreshTokenRepository;
    private AuthenticateUserCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createStub(UserRepository::class);
        $this->passwordHasher = $this->createStub(PasswordHasher::class);
        $this->tokenGenerator = $this->createStub(TokenGenerator::class);
        $this->refreshTokenRepository = $this->createStub(RefreshTokenRepository::class);
        $this->handler = new AuthenticateUserCommandHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->tokenGenerator,
            $this->refreshTokenRepository,
        );
    }

    public function testAuthenticatesSuccessfully(): void
    {
        $user = $this->createUser();

        $this->userRepository->method('findByEmail')->willReturn($user);
        $this->passwordHasher->method('verify')->willReturn(true);
        $this->tokenGenerator->method('generate')->willReturn(self::TOKEN);

        $response = ($this->handler)(new AuthenticateUserCommand(self::EMAIL, self::PASSWORD));

        $this->assertInstanceOf(AuthTokenResponse::class, $response);
        $this->assertSame(self::TOKEN, $response->token);
        $this->assertGreaterThan(0, $response->expiresIn);
        $this->assertNotNull($response->refreshToken);
    }

    public function testThrowsWhenUserNotFound(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new AuthenticateUserCommand(self::EMAIL, self::PASSWORD));
    }

    public function testThrowsWhenPasswordDoesNotMatch(): void
    {
        $user = $this->createUser();

        $this->userRepository->method('findByEmail')->willReturn($user);
        $this->passwordHasher->method('verify')->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new AuthenticateUserCommand(self::EMAIL, self::PASSWORD));
    }

    private function createUser(): User
    {
        $user = User::register(
            UserId::generate(),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );
        $user->pullDomainEvents();

        return $user;
    }
}
