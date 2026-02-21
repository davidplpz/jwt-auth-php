<?php

declare(strict_types=1);

namespace App\Auth\Application\Query\GetUserProfile;

final readonly class GetUserProfileQuery
{
    public function __construct(
        public string $userId,
    ) {
    }
}
