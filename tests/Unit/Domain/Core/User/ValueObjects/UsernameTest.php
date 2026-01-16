<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\User\ValueObjects;

use App\Domain\Core\User\Exceptions\InvalidUsernameException;
use App\Domain\Core\User\ValueObjects\Username;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UsernameTest extends TestCase
{
    #[Test]
    public function it_creates_valid_username(): void
    {
        $username = new Username('john_doe');

        $this->assertSame('john_doe', $username->value());
    }

    #[Test]
    public function it_compares_usernames(): void
    {
        $username1 = new Username('john_doe');
        $username2 = new Username('john_doe');
        $username3 = new Username('jane_doe');

        $this->assertTrue($username1->equals($username2));
        $this->assertFalse($username1->equals($username3));
    }

    #[Test]
    #[DataProvider('invalidUsernamesProvider')]
    public function it_throws_exception_for_invalid_username(string $invalidUsername, string $expectedMessagePart): void
    {
        $this->expectException(InvalidUsernameException::class);
        $this->expectExceptionMessage($expectedMessagePart);

        new Username($invalidUsername);
    }

    public static function invalidUsernamesProvider(): array
    {
        return [
            'too short' => ['ab', 'between'],
            'too long' => [str_repeat('a', 31), 'between'],
            'special characters' => ['john@doe', 'letters, numbers, and underscores'],
            'spaces' => ['john doe', 'letters, numbers, and underscores'],
            'hyphen' => ['john-doe', 'letters, numbers, and underscores'],
        ];
    }

    #[Test]
    #[DataProvider('validUsernamesProvider')]
    public function it_accepts_valid_username_formats(string $validUsername): void
    {
        $username = new Username($validUsername);

        $this->assertSame($validUsername, $username->value());
    }

    public static function validUsernamesProvider(): array
    {
        return [
            'minimum length' => ['abc'],
            'maximum length' => [str_repeat('a', 30)],
            'with numbers' => ['john123'],
            'with underscore' => ['john_doe'],
            'uppercase' => ['JohnDoe'],
            'mixed' => ['John_Doe_123'],
        ];
    }
}
