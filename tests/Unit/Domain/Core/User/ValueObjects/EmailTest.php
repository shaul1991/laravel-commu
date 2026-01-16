<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\User\ValueObjects;

use App\Domain\Core\User\Exceptions\InvalidEmailException;
use App\Domain\Core\User\ValueObjects\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function it_creates_valid_email(): void
    {
        $email = new Email('test@example.com');

        $this->assertSame('test@example.com', $email->value());
    }

    #[Test]
    public function it_extracts_domain(): void
    {
        $email = new Email('test@example.com');

        $this->assertSame('example.com', $email->domain());
    }

    #[Test]
    public function it_compares_emails(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    #[Test]
    #[DataProvider('invalidEmailsProvider')]
    public function it_throws_exception_for_invalid_email(string $invalidEmail): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email($invalidEmail);
    }

    public static function invalidEmailsProvider(): array
    {
        return [
            'empty string' => [''],
            'no @ symbol' => ['testexample.com'],
            'no domain' => ['test@'],
            'no local part' => ['@example.com'],
            'spaces' => ['test @example.com'],
            'invalid characters' => ['test<>@example.com'],
        ];
    }

    #[Test]
    #[DataProvider('validEmailsProvider')]
    public function it_accepts_valid_email_formats(string $validEmail): void
    {
        $email = new Email($validEmail);

        $this->assertSame($validEmail, $email->value());
    }

    public static function validEmailsProvider(): array
    {
        return [
            'simple' => ['test@example.com'],
            'with dots' => ['first.last@example.com'],
            'with plus' => ['test+tag@example.com'],
            'with numbers' => ['test123@example.com'],
            'subdomain' => ['test@sub.example.com'],
        ];
    }
}
