<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\RegisterUser;

use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\PlainPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Domain\Port\UserRepository;

final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $email = new Email($command->email);
        $password = new PlainPassword($command->password);

        if (null !== $this->userRepository->findByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($command->email);
        }

        $hashedPassword = $this->passwordHasher->hash($password);

        $user = User::register(UserId::generate(), $email, $hashedPassword);

        $this->userRepository->save($user);
    }
}
