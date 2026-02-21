<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Controller;

use App\Auth\Application\Command\AuthenticateUser\AuthenticateUserCommand;
use App\Auth\Application\Command\AuthenticateUser\AuthenticateUserCommandHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LoginController
{
    public function __construct(
        private AuthenticateUserCommandHandler $handler,
        private RateLimiterFactory $authLoginLimiter,
    ) {
    }

    #[Route('/api/auth/login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $limiter = $this->authLoginLimiter->create($request->getClientIp() ?? 'unknown');
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Too many requests.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        /** @var array{email?: string, password?: string} $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $response = ($this->handler)(new AuthenticateUserCommand(
            $data['email'] ?? '',
            $data['password'] ?? '',
        ));

        return new JsonResponse([
            'token' => $response->token,
            'refresh_token' => $response->refreshToken,
            'expires_in' => $response->expiresIn,
        ]);
    }
}
