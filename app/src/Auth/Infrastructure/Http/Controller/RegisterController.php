<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Controller;

use App\Auth\Application\Command\RegisterUser\RegisterUserCommand;
use App\Auth\Application\Command\RegisterUser\RegisterUserCommandHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RegisterController
{
    public function __construct(
        private RegisterUserCommandHandler $handler,
        private RateLimiterFactory $authRegisterLimiter,
    ) {
    }

    #[Route('/api/auth/register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $limiter = $this->authRegisterLimiter->create($request->getClientIp() ?? 'unknown');
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Too many requests.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        /** @var array{email?: string, password?: string} $data */
        $data = json_decode($request->getContent(), true) ?? [];

        ($this->handler)(new RegisterUserCommand(
            $data['email'] ?? '',
            $data['password'] ?? '',
        ));

        return new JsonResponse(['message' => 'User registered successfully.'], Response::HTTP_CREATED);
    }
}
