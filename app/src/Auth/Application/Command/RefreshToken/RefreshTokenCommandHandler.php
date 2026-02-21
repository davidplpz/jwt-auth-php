<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\RefreshToken;

use App\Auth\Application\DTO\AuthTokenResponse;
use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Model\RefreshToken as RefreshTokenModel;
use App\Auth\Domain\Port\RefreshTokenRepository;
use App\Auth\Domain\Port\UserRepository;

final readonly class RefreshTokenCommandHandler
{
    private const TOKEN_TTL_SECONDS = 3600;
    private const REFRESH_TOKEN_TTL = '+30 days';

    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private UserRepository $userRepository,
        private TokenGenerator $tokenGenerator,
    ) {
    }

    public function __invoke(RefreshTokenCommand $command): AuthTokenResponse
    {
        $oldRefreshToken = $this->refreshTokenRepository->findByToken($command->refreshToken);

        if (null === $oldRefreshToken || !$oldRefreshToken->isValid()) {
            throw InvalidCredentialsException::create();
        }

        $user = $this->userRepository->findById($oldRefreshToken->userId());

        if (null === $user) {
            throw InvalidCredentialsException::create();
        }

        $oldRefreshToken->revoke();
        $this->refreshTokenRepository->save($oldRefreshToken);

        $newJwt = $this->tokenGenerator->generate($user);

        $newRefreshToken = RefreshTokenModel::create(
            bin2hex(random_bytes(32)),
            $user->id(),
            new \DateTimeImmutable(self::REFRESH_TOKEN_TTL),
        );
        $this->refreshTokenRepository->save($newRefreshToken);

        return new AuthTokenResponse($newJwt, self::TOKEN_TTL_SECONDS, $newRefreshToken->token());
    }
}
