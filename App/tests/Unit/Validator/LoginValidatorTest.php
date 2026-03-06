<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Validator\LoginValidator;
use Validator\FormValidator;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationLogin;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmpty;

/**
 * Unit tests for LoginValidator
 */
#[CoversClass(LoginValidator::class)]
#[CoversClass(FormValidator::class)]
#[CoversClass(ExceptionValidationLogin::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
class LoginValidatorTest extends TestCase
{
    private LoginValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LoginValidator();
    }

    #[Test]
    public function validCredentialsAreAccepted(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => 'SecurePass123'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function validEmailsProvider(): array
    {
        return [
            'student_email' => ['jean.dupont@etu.univ-amu.fr'],
            'professor_email' => ['prof.martin@univ-amu.fr'],
            'with_number' => ['jean.dupont.1@etu.univ-amu.fr'],
            'three_parts' => ['jean.marie.dupont@etu.univ-amu.fr']
        ];
    }

    #[Test]
    #[DataProvider('validEmailsProvider')]
    public function variousValidEmailsAreAccepted(string $email): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'email' => $email,
            'password' => 'AnyPassword'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidEmailsProvider(): array
    {
        return [
            'no_at' => ['jeandupont.etu.univ-amu.fr'],
            'missing_tld' => ['jean.dupont@etu'],
            'spaces' => ['jean dupont@etu.univ-amu.fr'],
            'double_at' => ['jean@@dupont.etu.univ-amu.fr'],
            'starts_with_dot' => ['.jean@etu.univ-amu.fr'],
            'ends_with_dot' => ['jean.@etu.univ-amu.fr']
        ];
    }

    #[Test]
    #[DataProvider('invalidEmailsProvider')]
    public function invalidEmailsAreRejected(string $email): void
    {
        $this->expectException(ExceptionValidationLogin::class);

        $data = [
            'email' => $email,
            'password' => 'AnyPassword'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function emptyEmailIsRejected(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [
            'email' => '',
            'password' => 'Password123'
        ];

        $this->validator->escape($data);
    }

    #[Test]
    public function emptyPasswordIsRejected(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => ''
        ];

        $this->validator->escape($data);
    }

    #[Test]
    public function bothFieldsEmptyThrowsException(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [
            'email' => '',
            'password' => ''
        ];

        $this->validator->escape($data);
    }

    #[Test]
    public function missingFieldsThrowException(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [];
        $this->validator->escape($data);
    }

    #[Test]
    public function escapeMethodSanitizesInput(): void
    {
        $data = [
            'email' => '<script>alert("xss")</script>@etu.univ-amu.fr',
            'password' => '<b>bold</b>'
        ];

        $escaped = $this->validator->escape($data);

        $this->assertStringContainsString('&lt;', $escaped['email']);
        $this->assertStringContainsString('&lt;', $escaped['password']);
        $this->assertStringNotContainsString('<script>', $escaped['email']);
        $this->assertStringNotContainsString('<b>', $escaped['password']);
    }

    #[Test]
    public function validatorDoesNotCheckPasswordStrength(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => '123' // Short password, but login doesn't check strength
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function validatorHasCorrectRequiredFields(): void
    {
        $reflection = new ReflectionClass($this->validator);
        $property = $reflection->getProperty('required');
        $property->setAccessible(true);
        $required = $property->getValue($this->validator);

        $this->assertContains('email', $required);
        $this->assertContains('password', $required);
        $this->assertCount(2, $required);
    }

    #[Test]
    public function exceptionContainsAppropriateMessage(): void
    {
        try {
            $data = [
                'email' => 'invalid-email',
                'password' => 'password'
            ];

            $escaped = $this->validator->escape($data);
            $this->validator->validate($escaped);

            $this->fail('Should have thrown exception');
        } catch (ExceptionValidationLogin $e) {
            $message = $e->getAdditionalInfo();
            $this->assertNotEmpty($message);
            $this->assertIsString($message);
        }
    }

    #[Test]
    public function whitespaceIsPreservedInPassword(): void
    {
        $data = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => '  password with spaces  '
        ];

        $escaped = $this->validator->escape($data);

        // Escape should not trim password
        $this->assertEquals('  password with spaces  ', $escaped['password']);
    }

    #[Test]
    public function emailIsCaseInsensitiveInValidation(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'email' => 'Jean.Dupont@ETU.UNIV-AMU.FR',
            'password' => 'password'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }
}
