<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Validator\ResetPasswordValidator;
use Validator\FormValidator;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;

/**
 * Unit tests for ResetPasswordValidator
 */
#[CoversClass(ResetPasswordValidator::class)]
#[CoversClass(FormValidator::class)]
#[CoversClass(ExceptionValidationResetPassword::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
class ResetPasswordValidatorTest extends TestCase
{
    private ResetPasswordValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ResetPasswordValidator();
    }

    #[Test]
    public function validPasswordsAreAccepted(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'pwdnew' => 'SecurePass123',
            'pwdverif' => 'SecurePass123'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function validPasswordsProvider(): array
    {
        return [
            'eight_chars' => ['12345678', '12345678'],
            'long_password' => ['ThisIsAVeryLongPassword123456', 'ThisIsAVeryLongPassword123456'],
            'with_special_chars' => ['Pass@word123!', 'Pass@word123!'],
            'mixed_case' => ['AbCdEfGh', 'AbCdEfGh']
        ];
    }

    #[Test]
    #[DataProvider('validPasswordsProvider')]
    public function variousValidPasswordsAreAccepted(string $pwd1, string $pwd2): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'pwdnew' => $pwd1,
            'pwdverif' => $pwd2
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function shortPasswordsProvider(): array
    {
        return [
            'one_char' => ['a', 'a'],
            'seven_chars' => ['1234567', '1234567'],
            'spaces' => ['       ', '       ']
        ];
    }

    #[Test]
    #[DataProvider('shortPasswordsProvider')]
    public function shortPasswordsAreRejected(string $pwd1, string $pwd2): void
    {
        $this->expectException(ExceptionValidationResetPassword::class);

        $data = [
            'pwdnew' => $pwd1,
            'pwdverif' => $pwd2
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function mismatchedPasswordsAreRejected(): void
    {
        $this->expectException(ExceptionValidationResetPassword::class);

        $data = [
            'pwdnew' => 'Password123',
            'pwdverif' => 'DifferentPass456'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function mismatchedPasswordsProvider(): array
    {
        return [
            'different_length' => ['12345678', '123456789'],
            'different_case' => ['PASSWORD', 'password'],
            'trailing_space' => ['password ', 'password'],
            'leading_space' => [' password', 'password']
        ];
    }

    #[Test]
    #[DataProvider('mismatchedPasswordsProvider')]
    public function subtlyDifferentPasswordsAreRejected(string $pwd1, string $pwd2): void
    {
        $this->expectException(ExceptionValidationResetPassword::class);

        $data = [
            'pwdnew' => $pwd1,
            'pwdverif' => $pwd2
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function emptyFieldsThrowEmptysException(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [
            'pwdnew' => '',
            'pwdverif' => ''
        ];

        $this->validator->escape($data);
    }

    #[Test]
    public function missingFieldsThrowEmptysException(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [];
        $this->validator->escape($data);
    }

    #[Test]
    public function escapeMethodSanitizesInput(): void
    {
        $data = [
            'pwdnew' => '<script>alert("xss")</script>',
            'pwdverif' => '<script>alert("xss")</script>'
        ];

        $escaped = $this->validator->escape($data);

        $this->assertStringContainsString('&lt;', $escaped['pwdnew']);
        $this->assertStringContainsString('&gt;', $escaped['pwdnew']);
        $this->assertStringNotContainsString('<script>', $escaped['pwdnew']);
    }

    #[Test]
    public function exceptionContainsFieldInformation(): void
    {
        try {
            $data = [
                'pwdnew' => 'short',
                'pwdverif' => 'short'
            ];

            $escaped = $this->validator->escape($data);
            $this->validator->validate($escaped);

            $this->fail('Should have thrown exception');
        } catch (ExceptionValidationResetPassword $e) {
            $this->assertEquals('pwdnew', $e->getField());
            $this->assertNotEmpty($e->getAdditionalInfo());
        }
    }

    #[Test]
    public function exceptionForMismatchContainsCorrectField(): void
    {
        try {
            $data = [
                'pwdnew' => 'ValidPassword123',
                'pwdverif' => 'DifferentPassword456'
            ];

            $escaped = $this->validator->escape($data);
            $this->validator->validate($escaped);

            $this->fail('Should have thrown exception');
        } catch (ExceptionValidationResetPassword $e) {
            $this->assertEquals('pwdverif', $e->getField());
            $this->assertStringContainsString('correspondent', $e->getAdditionalInfo());
        }
    }

    #[Test]
    public function validatorHasCorrectRequiredFields(): void
    {
        $reflection = new \ReflectionClass($this->validator);
        $property = $reflection->getProperty('required');
        $property->setAccessible(true);
        $required = $property->getValue($this->validator);

        $this->assertContains('pwdnew', $required);
        $this->assertContains('pwdverif', $required);
        $this->assertCount(2, $required);
    }
}
