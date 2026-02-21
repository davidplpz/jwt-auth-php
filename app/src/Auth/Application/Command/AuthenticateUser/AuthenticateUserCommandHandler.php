<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\AuthenticateUser;

use App\Auth\Application\DTO\AuthTokenResponse;
use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\PlainPassword;
use App\Auth\Domain\Model\RefreshToken;
use App\Auth\Domain\Port\RefreshTokenRepository;
use App\Auth\Domain\Port\UserRepository;

final readonly class AuthenticateUserCommandHandler
{
    private const TOKEN_TTL_SECONDS = 3600;
    private const REFRESH_TOKEN_TTL = '+30 days';

    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private TokenGenerator $tokenGenerator,
        private RefreshTokenRepository $refreshTokenRepository,
    ) {
    }

    public function __invoke(AuthenticateUserCommand $command): AuthTokenResponse
    {
        $email = new Email($command->email);
        $plainPassword = new PlainPassword($command->password);

        $user = $this->userRepository->findByEmail($email);

        if (null === $user) {
            throw InvalidCredentialsException::create();
        }

        if (!$this->passwordHasher->verify($plainPassword, $user->password())) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->tokenGenerator->generate($user);

        $refreshToken = RefreshToken::create(
            bin2hex(random_bytes(32)),
            $user->id(),
            new \DateTimeImmutable(self::REFRESH_TOKEN_TTL),
        );
        $this->refreshTokenRepository->save($refreshToken);

        return new AuthTokenResponse($token, self::TOKEN_TTL_SECONDS, $refreshToken->token());
    }
}
