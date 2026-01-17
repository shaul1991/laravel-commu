<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\Services\PasswordHasherInterface;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\Username;
use App\Infrastructure\Persistence\Eloquent\UserModel;

final class LoginUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(LoginUserInput $input): LoginResult
    {
        // Try to find user by email or username
        $user = $this->findUser($input->emailOrUsername);

        if (! $user) {
            throw new InvalidCredentialsException;
        }

        // Verify password
        if (! $user->password()->verify($input->password, $this->passwordHasher)) {
            throw new InvalidCredentialsException;
        }

        // Get Eloquent model for token generation
        $userModel = UserModel::where('uuid', $user->id()->value())->firstOrFail();

        // Generate API token
        $token = $userModel->createToken('auth-token')->plainTextToken;

        return new LoginResult($user, $token);
    }

    private function findUser(string $emailOrUsername): ?User
    {
        // Try email first
        if (filter_var($emailOrUsername, FILTER_VALIDATE_EMAIL)) {
            return $this->userRepository->findByEmail(new Email($emailOrUsername));
        }

        // Try username
        try {
            return $this->userRepository->findByUsername(new Username($emailOrUsername));
        } catch (\Throwable) {
            return null;
        }
    }
}
