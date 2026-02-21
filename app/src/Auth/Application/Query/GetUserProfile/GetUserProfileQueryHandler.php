<?php

declare(strict_types=1);

namespace App\Auth\Application\Query\GetUserProfile;

use App\Auth\Application\DTO\UserProfileResponse;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\UserRepository;

final readonly class GetUserProfileQueryHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(GetUserProfileQuery $query): UserProfileResponse
    {
        $user = $this->userRepository->findById(new UserId($query->userId));

        if (null === $user) {
            throw new \DomainException(
                sprintf('User with id <%s> not found.', $query->userId)
            );
        }

        return new UserProfileResponse(
            $user->id()->value(),
            $user->email()->value(),
        );
    }
}
