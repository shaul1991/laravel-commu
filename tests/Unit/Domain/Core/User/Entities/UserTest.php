<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\User\Entities;

use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\Events\UserRegistered;
use App\Domain\Core\User\Events\UserUpdated;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\Password;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Domain\Core\User\ValueObjects\Username;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function it_registers_user_with_domain_event(): void
    {
        $user = User::register(
            id: UserId::fromString('test-user-id'),
            email: new Email('test@example.com'),
            username: new Username('testuser'),
            password: Password::fromHash('hashed_password'),
            name: 'Test User'
        );

        $this->assertSame('test-user-id', $user->id()->value());
        $this->assertSame('test@example.com', $user->email()->value());
        $this->assertSame('testuser', $user->username()->value());
        $this->assertSame('Test User', $user->name());
        $this->assertNull($user->bio());
        $this->assertNull($user->avatarUrl());
        $this->assertFalse($user->isEmailVerified());

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserRegistered::class, $events[0]);
    }

    #[Test]
    public function it_verifies_email(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->isEmailVerified());

        $user->verifyEmail();

        $this->assertTrue($user->isEmailVerified());
        $this->assertNotNull($user->emailVerifiedAt());
    }

    #[Test]
    public function it_does_not_verify_email_twice(): void
    {
        $user = $this->createUser();
        $user->verifyEmail();

        $firstVerifiedAt = $user->emailVerifiedAt();

        $user->verifyEmail();

        $this->assertSame($firstVerifiedAt, $user->emailVerifiedAt());
    }

    #[Test]
    public function it_updates_profile(): void
    {
        $user = $this->createUser();
        $user->pullDomainEvents(); // Clear registration event

        $user->updateProfile('New Name', 'New bio text');

        $this->assertSame('New Name', $user->name());
        $this->assertSame('New bio text', $user->bio());

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserUpdated::class, $events[0]);
    }

    #[Test]
    public function it_updates_avatar(): void
    {
        $user = $this->createUser();

        $user->updateAvatar('https://example.com/avatar.jpg');

        $this->assertSame('https://example.com/avatar.jpg', $user->avatarUrl());
    }

    #[Test]
    public function it_changes_password(): void
    {
        $user = $this->createUser();
        $newPassword = Password::fromHash('new_hashed_password');

        $user->changePassword($newPassword);

        $this->assertSame('new_hashed_password', $user->password()->value());
    }

    #[Test]
    public function it_changes_email_and_clears_verification(): void
    {
        $user = $this->createUser();
        $user->verifyEmail();

        $this->assertTrue($user->isEmailVerified());

        $user->changeEmail(new Email('new@example.com'));

        $this->assertSame('new@example.com', $user->email()->value());
        $this->assertFalse($user->isEmailVerified());
    }

    private function createUser(): User
    {
        return User::register(
            id: UserId::fromString('test-user-id'),
            email: new Email('test@example.com'),
            username: new Username('testuser'),
            password: Password::fromHash('hashed_password'),
            name: 'Test User'
        );
    }
}
