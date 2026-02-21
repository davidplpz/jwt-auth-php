<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Infrastructure\Security;

use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Infrastructure\Security\JwtTokenDecoder;
use App\Auth\Infrastructure\Security\JwtTokenGenerator;
use PHPUnit\Framework\TestCase;

final class JwtTokenTest extends TestCase
{
    private const SECRET = 'a-secret-key-that-is-long-enough-for-hmac-sha256!';
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const EMAIL = 'user@example.com';

    private JwtTokenGenerator $generator;
    private JwtTokenDecoder $decoder;

    protected function setUp(): void
    {
        $this->generator = new JwtTokenGenerator(self::SECRET);
        $this->decoder = new JwtTokenDecoder(self::SECRET);
    }

    public function testGeneratesValidJwtToken(): void
    {
        $user = $this->createUser();
        $token = $this->generator->generate($user);

        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function testDecodesGeneratedToken(): void
    {
        $user = $this->createUser();
        $token = $this->generator->generate($user);

        $payload = $this->decoder->decode($token);

        $this->assertSame(self::UUID, $payload['user_id']);
        $this->assertSame(self::EMAIL, $payload['email']);
    }

    public function testRejectsInvalidToken(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $this->decoder->decode('invalid.token.value');
    }

    public function testRejectsTokenWithWrongSecret(): void
    {
        $user = $this->createUser();
        $token = $this->generator->generate($user);

        $wrongDecoder = new JwtTokenDecoder('a-different-secret-key-that-is-also-long-enough!!');

        $this->expectException(InvalidCredentialsException::class);

        $wrongDecoder->decode($token);
    }

    public function testRejectsExpiredToken(): void
    {
        $expiredClock = new class implements \Psr\Clock\ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('+2 hours');
            }
        };

        $user = $this->createUser();
        $token = $this->generator->generate($user);
        $futureDecoder = new JwtTokenDecoder(self::SECRET, $expiredClock);

        $this->expectException(InvalidCredentialsException::class);

        $futureDecoder->decode($token);
    }

    private function createUser(): User
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email(self::EMAIL),
            HashedPassword::fromHash('$2y$10$hash'),
        );
        $user->pullDomainEvents();

        return $user;
    }
}
