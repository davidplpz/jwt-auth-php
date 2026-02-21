<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Controller;

use App\Auth\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Auth\Application\Command\RefreshToken\RefreshTokenCommandHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RefreshController
{
    public function __construct(private RefreshTokenCommandHandler $handler)
    {
    }

    #[Route('/api/auth/refresh', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{refresh_token?: string} $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $response = ($this->handler)(new RefreshTokenCommand(
            $data['refresh_token'] ?? '',
        ));

        return new JsonResponse([
            'token' => $response->token,
            'refresh_token' => $response->refreshToken,
            'expires_in' => $response->expiresIn,
        ]);
    }
}
