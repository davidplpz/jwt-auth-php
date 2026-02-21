<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\PlainPassword;

interface PasswordHasher
{
    public function hash(PlainPassword $password): HashedPassword;

    public function verify(PlainPassword $plain, HashedPassword $hashed): bool;
}
