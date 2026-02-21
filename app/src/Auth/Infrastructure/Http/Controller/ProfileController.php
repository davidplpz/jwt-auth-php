<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Controller;

use App\Auth\Application\Query\GetUserProfile\GetUserProfileQuery;
use App\Auth\Application\Query\GetUserProfile\GetUserProfileQueryHandler;
use App\Auth\Infrastructure\Security\JwtUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ProfileController
{
    public function __construct(
        private GetUserProfileQueryHandler $handler,
        private Security $security,
    ) {
    }

    #[Route('/api/auth/profile', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var JwtUser $user */
        $user = $this->security->getUser();

        $response = ($this->handler)(new GetUserProfileQuery($user->getUserIdentifier()));

        return new JsonResponse([
            'id' => $response->id,
            'email' => $response->email,
        ]);
    }
}
