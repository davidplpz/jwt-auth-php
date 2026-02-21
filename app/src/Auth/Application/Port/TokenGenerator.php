<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\Model\User;

interface TokenGenerator
{
    public function generate(User $user): string;
}
