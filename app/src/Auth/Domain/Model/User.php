<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\AggregateRoot;

final class User extends AggregateRoot
{
    private function __construct(
        private readonly UserId $id,
        private readonly Email $email,
        private readonly HashedPassword $password,
    ) {
    }

    public static function register(UserId $id, Email $email, HashedPassword $password): self
    {
        $user = new self($id, $email, $password);

        $user->record(new UserRegistered($id->value(), $email->value()));

        return $user;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }
}
