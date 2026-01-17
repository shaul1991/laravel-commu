<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Entities;

use App\Domain\Core\Shared\AggregateRoot;
use App\Domain\Core\User\Events\UserRegistered;
use App\Domain\Core\User\Events\UserUpdated;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\Password;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Domain\Core\User\ValueObjects\Username;
use DateTimeImmutable;

class User extends AggregateRoot
{
    private function __construct(
        private readonly UserId $id,
        private Email $email,
        private Username $username,
        private Password $password,
        private string $name,
        private ?string $bio,
        private ?string $avatarUrl,
        private ?DateTimeImmutable $emailVerifiedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function register(
        UserId $id,
        Email $email,
        Username $username,
        Password $password,
        string $name
    ): self {
        $now = new DateTimeImmutable;

        $user = new self(
            id: $id,
            email: $email,
            username: $username,
            password: $password,
            name: $name,
            bio: null,
            avatarUrl: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now
        );

        $user->recordEvent(new UserRegistered($id, $email, $now));

        return $user;
    }

    /**
     * Reconstitute a User entity from persistence (no events recorded)
     */
    public static function reconstitute(
        UserId $id,
        Email $email,
        Username $username,
        Password $password,
        string $name,
        ?string $bio,
        ?string $avatarUrl,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            email: $email,
            username: $username,
            password: $password,
            name: $name,
            bio: $bio,
            avatarUrl: $avatarUrl,
            emailVerifiedAt: $emailVerifiedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function verifyEmail(): void
    {
        if ($this->emailVerifiedAt !== null) {
            return;
        }

        $this->emailVerifiedAt = new DateTimeImmutable;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function updateProfile(string $name, ?string $bio): void
    {
        $this->name = $name;
        $this->bio = $bio;
        $this->updatedAt = new DateTimeImmutable;

        $this->recordEvent(new UserUpdated($this->id, new DateTimeImmutable));
    }

    public function updateAvatar(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function changePassword(Password $newPassword): void
    {
        $this->password = $newPassword;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function changeEmail(Email $newEmail): void
    {
        $this->email = $newEmail;
        $this->emailVerifiedAt = null;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    // Getters
    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function bio(): ?string
    {
        return $this->bio;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function emailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
