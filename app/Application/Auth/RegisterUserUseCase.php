<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\Services\PasswordHasherInterface;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\Password;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Domain\Core\User\ValueObjects\Username;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(RegisterUserInput $input): User
    {
        $email = new Email($input->email);
        $username = new Username($input->username);

        // Check for duplicates
        if ($this->userRepository->existsByEmail($email)) {
            throw new \DomainException('Email already exists');
        }

        if ($this->userRepository->existsByUsername($username)) {
            throw new \DomainException('Username already exists');
        }

        // Create user
        $user = User::register(
            id: UserId::generate(),
            email: $email,
            username: $username,
            password: Password::fromPlainText($input->password, $this->passwordHasher),
            name: $input->name
        );

        // Save to repository
        $this->userRepository->save($user);

        return $user;
    }
}
