<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Application\Command;

use App\Auth\Application\Command\RegisterUser\RegisterUserCommand;
use App\Auth\Application\Command\RegisterUser\RegisterUserCommandHandler;
use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Exception\WeakPasswordException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\PlainPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\UserRepository;
use PHPUnit\Framework\TestCase;

final class RegisterUserCommandHandlerTest extends TestCase
{
    private const EMAIL = 'user@example.com';
    private const PASSWORD = 'Str0ng!Pass';
    private const HASH = '$2y$10$hashedpasswordvalue1234567890abcdefghijklmnopqrst';

    private UserRepository&\PHPUnit\Framework\MockObject\MockObject $userRepository;
    private PasswordHasher&\PHPUnit\Framework\MockObject\Stub $passwordHasher;
    private RegisterUserCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createStub(PasswordHasher::class);
        $this->handler = new RegisterUserCommandHandler(
            $this->userRepository,
            $this->passwordHasher,
        );
    }

    public function testRegistersUserSuccessfully(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $this->passwordHasher
            ->method('hash')
            ->willReturn(HashedPassword::fromHash(self::HASH));

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        ($this->handler)(new RegisterUserCommand(self::EMAIL, self::PASSWORD));
    }

    public function testThrowsWhenEmailAlreadyExists(): void
    {
        $existingUser = User::register(
            UserId::generate(),
            new Email(self::EMAIL),
            HashedPassword::fromHash(self::HASH),
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingUser);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(UserAlreadyExistsException::class);

        ($this->handler)(new RegisterUserCommand(self::EMAIL, self::PASSWORD));
    }

    public function testThrowsWhenPasswordIsWeak(): void
    {
        $this->userRepository
            ->expects($this->never())
            ->method('findByEmail');

        $this->expectException(WeakPasswordException::class);

        ($this->handler)(new RegisterUserCommand(self::EMAIL, 'weak'));
    }
}
