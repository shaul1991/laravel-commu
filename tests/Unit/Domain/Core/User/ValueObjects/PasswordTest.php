<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\User\ValueObjects;

use App\Domain\Core\User\Exceptions\WeakPasswordException;
use App\Domain\Core\User\Services\PasswordHasherInterface;
use App\Domain\Core\User\ValueObjects\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PasswordTest extends TestCase
{
    private PasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = new class implements PasswordHasherInterface
        {
            public function hash(string $plainPassword): string
            {
                return 'hashed_'.$plainPassword;
            }

            public function verify(string $plainPassword, string $hashedPassword): bool
            {
                return $hashedPassword === 'hashed_'.$plainPassword;
            }
        };
    }

    #[Test]
    public function it_creates_password_from_plain_text(): void
    {
        $password = Password::fromPlainText('Password1!', $this->hasher);

        $this->assertSame('hashed_Password1!', $password->value());
    }

    #[Test]
    public function it_creates_password_from_hash(): void
    {
        $password = Password::fromHash('existing_hash');

        $this->assertSame('existing_hash', $password->value());
    }

    #[Test]
    public function it_verifies_password(): void
    {
        $password = Password::fromPlainText('Password1!', $this->hasher);

        $this->assertTrue($password->verify('Password1!', $this->hasher));
        $this->assertFalse($password->verify('WrongPassword1!', $this->hasher));
    }

    #[Test]
    #[DataProvider('weakPasswordsProvider')]
    public function it_throws_exception_for_weak_password(string $weakPassword, string $expectedMessagePart): void
    {
        $this->expectException(WeakPasswordException::class);
        $this->expectExceptionMessage($expectedMessagePart);

        Password::fromPlainText($weakPassword, $this->hasher);
    }

    public static function weakPasswordsProvider(): array
    {
        return [
            'too short' => ['Pass1!', 'at least 8 characters'],
            'no letter' => ['12345678!', 'at least one letter'],
            'no number' => ['Password!', 'at least one number'],
            'no special char' => ['Password1', 'at least one special character'],
        ];
    }

    #[Test]
    #[DataProvider('strongPasswordsProvider')]
    public function it_accepts_strong_passwords(string $strongPassword): void
    {
        $password = Password::fromPlainText($strongPassword, $this->hasher);

        $this->assertSame('hashed_'.$strongPassword, $password->value());
    }

    public static function strongPasswordsProvider(): array
    {
        return [
            'basic' => ['Password1!'],
            'longer' => ['MySecurePassword123!'],
            'complex' => ['P@$$w0rd#2024'],
            'mixed special' => ['Test1234!@#$'],
        ];
    }
}
