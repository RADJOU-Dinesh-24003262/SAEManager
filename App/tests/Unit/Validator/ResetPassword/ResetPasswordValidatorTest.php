<?php

namespace Tests\Unit\Validator\ResetPassword;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Validator\ResetPassword\ResetPasswordValidator;
use Validator\FormValidator;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationResetPassword;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmpty;

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
            'pwdnew' => 'SecureP@ss1234!',
            'pwdverif' => 'SecureP@ss1234!'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function validPasswordsProvider(): array
    {
        return [
            'exactly_12_chars' => ['Valid12Chars!', 'Valid12Chars!'],
            'long_password' => ['ThisIsAVeryLongPassword123456!', 'ThisIsAVeryLongPassword123456!'],
            'with_special_chars' => ['Pass@word123!', 'Pass@word123!'],
            'mixed_case_complex' => ['MixedCase123@', 'MixedCase123@']
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
            'pwdnew' => 'P@ssword2026!',
            'pwdverif' => 'DifferentP@ssword2026!'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function mismatchedPasswordsProvider(): array
    {
        return [
            'different_length' => ['ValidP@ss123!', 'ValidP@ss1234!'],
            'different_case' => ['ValidP@ss123!', 'validp@ss123!'],
            'trailing_space' => ['ValidP@ss123! ', 'ValidP@ss123!'],
            'leading_space' => [' ValidP@ss123!', 'ValidP@ss123!']
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

            $escaped = $this -> validator -> escape($data);
            $this -> validator -> validate($escaped);

            $this -> fail('Should have thrown exception');
        } catch (ExceptionValidationResetPassword $e) {
            $this -> assertEquals('pwdnew', $e -> getField());
            $this -> assertNotEmpty($e -> getAdditionalInfo());
        }
    }

    #[Test]
    public function exceptionForMismatchContainsCorrectField(): void
    {
        try {
            $data = [
                'pwdnew' => 'ValidP@ssword123!',
                'pwdverif' => 'DifferentP@ssword456!'
            ];

            $escaped = $this -> validator -> escape($data);
            $this -> validator -> validate($escaped);

            $this -> fail('Should have thrown exception');
        } catch (ExceptionValidationResetPassword $e) {
            $this -> assertEquals('pwdverif', $e -> getField());
            $this -> assertStringContainsString('correspondent', $e -> getAdditionalInfo());
        }
    }

    #[Test]
    public function validatorHasCorrectRequiredFields(): void
    {
        $reflection = new ReflectionClass($this -> validator);
        $property = $reflection -> getProperty('required');
        $property -> setAccessible(true);
        $required = $property -> getValue($this -> validator);

        $this -> assertContains('pwdnew', $required);
        $this -> assertContains('pwdverif', $required);
        $this -> assertCount(2, $required);
    }
}
