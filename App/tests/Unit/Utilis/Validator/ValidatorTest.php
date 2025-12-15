<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Validator\FormValidator;
use Validator\LoginValidator;
use Validator\ResetPasswordValidator;
use Validator\ForgotPasswordValidator;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Core\includes\exception\ExceptionValidation\ExceptionValidationForgotPassword;
use Core\includes\exception\ExceptionSpam;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\Utilis\SessionService;

/**
 * Tests complets pour tous les validateurs
 */
#[CoversClass(LoginValidator::class)]
#[CoversClass(ResetPasswordValidator::class)]
#[CoversClass(ForgotPasswordValidator::class)]
#[CoversClass(ExceptionValidationLogin::class)]
#[CoversClass(ExceptionValidationResetPassword::class)]
#[CoversClass(SessionService::class)]
#[CoversClass(ExceptionSpam::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
#[CoversClass(ExceptionValidationForgotPassword::class)]
class ValidatorTest extends TestCase
{
    // ========================================
    // Tests pour LoginValidator
    // ========================================
    #[Test]
    public function loginValidatorAcceptsValidEmail(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new LoginValidator();
        $data = $validator->escape(
            [
            'email' => 'test@univ-amu.fr',
            'password' => 'password123'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    public function loginValidatorThrowsExceptionForEmptyFields(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $validator = new LoginValidator();
        $validator->escape(
            [
            'email' => '',
            'password' => ''
            ]
        );
    }

    #[Test]
    #[DataProvider('invalidEmailsProvider')]
    public function loginValidatorRejectsInvalidEmails(string $email): void
    {
        $this->expectException(ExceptionValidationLogin::class);

        $validator = new LoginValidator();
        $data = $validator->escape(
            [
            'email' => $email,
            'password' => 'password123'
            ]
        );

        $validator->validate($data);
    }

    public static function invalidEmailsProvider(): array
    {
        return [
            'no_at' => ['testuniv-amu.fr'],
            'no_domain' => ['test@'],
            'no_local' => ['@univ-amu.fr'],
            'spaces' => ['test @univ-amu.fr'],
            'double_at' => ['test@@univ-amu.fr'],
            'special_chars' => ['test<>@univ-amu.fr'],
            'just_text' => ['notanemail']
        ];
    }

    #[Test]
    public function loginValidatorEscapesHtmlInEmail(): void
    {
        $validator = new LoginValidator();
        $data = $validator->escape(
            [
            'email' => 'test<script>alert("xss")</script>@test.fr',
            'password' => '<b>password</b>'
            ]
        );

        $this->assertStringNotContainsString('<script>', $data['email']);
        $this->assertStringNotContainsString('<b>', $data['password']);
    }

    // ========================================
    // Tests pour ResetPasswordValidator
    // ========================================
    #[Test]
    public function resetPasswordValidatorAcceptsValidPasswords(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ResetPasswordValidator();
        $data = $validator->escape(
            [
            'pwdnew' => 'NewPassword123',
            'pwdverif' => 'NewPassword123'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    public function resetPasswordValidatorRejectsShortPassword(): void
    {
        $this->expectException(ExceptionValidationResetPassword::class);
        $this->expectExceptionMessage('au moins 8 caractères');

        $validator = new ResetPasswordValidator();
        $data = $validator->escape(
            [
            'pwdnew' => 'short',
            'pwdverif' => 'short'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    public function resetPasswordValidatorRejectsMismatchedPasswords(): void
    {
        $this->expectException(ExceptionValidationResetPassword::class);
        $this->expectExceptionMessage('ne correspondent pas');

        $validator = new ResetPasswordValidator();
        $data = $validator->escape(
            [
            'pwdnew' => 'Password123',
            'pwdverif' => 'DifferentPassword123'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    #[DataProvider('validPasswordsProvider')]
    public function resetPasswordValidatorAcceptsVariousValidPasswords(string $password): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ResetPasswordValidator();
        $data = $validator->escape(
            [
            'pwdnew' => $password,
            'pwdverif' => $password
            ]
        );

        $validator->validate($data);
    }

    public static function validPasswordsProvider(): array
    {
        return [
            'exactly_8_chars' => ['12345678'],
            'with_special' => ['P@ssw0rd!'],
            'long_password' => ['ThisIsAVeryLongPasswordWithMoreThan20Characters'],
            'with_spaces' => ['My Pass Word 123'],
            'numbers_only' => ['12345678'],
            'mixed_case' => ['AbCdEfGh']
        ];
    }

    #[Test]
    public function resetPasswordValidatorThrowsExceptionForEmptyFields(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $validator = new ResetPasswordValidator();
        $validator->escape(
            [
            'pwdnew' => '',
            'pwdverif' => ''
            ]
        );
    }

    // ========================================
    // Tests pour ForgotPasswordValidator
    // ========================================
    #[Test]
    public function forgotPasswordValidatorAcceptsValidEmail(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ForgotPasswordValidator();
        $data = $validator->escape(
            [
            'email' => 'test@univ-amu.fr'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    public function forgotPasswordValidatorRejectsInvalidEmail(): void
    {
        $this->expectException(ExceptionValidationForgotPassword::class);

        $validator = new ForgotPasswordValidator();
        $data = $validator->escape(
            [
            'email' => 'not-an-email'
            ]
        );

        $validator->validate($data);
    }

    #[Test]
    public function forgotPasswordValidatorThrowsSpamExceptionWhenTooManyRequests(): void
    {
        $this->expectException(ExceptionSpam::class);
        $this->expectExceptionMessage('au moins 2 minutes');

        // Simuler une demande récente
        $_SESSION['last_forgot_password_request'] = time();

        $validator = new ForgotPasswordValidator();
        $data = $validator->escape(
            [
            'email' => 'test@univ-amu.fr'
            ]
        );

        $validator->validate($data);

        unset($_SESSION['last_forgot_password_request']);
    }

    #[Test]
    public function forgotPasswordValidatorAllowsRequestAfterCooldown(): void
    {
        $this->expectNotToPerformAssertions();

        // Simuler une demande il y a plus de 2 minutes
        $_SESSION['last_forgot_password_request'] = time() - 121;

        $validator = new ForgotPasswordValidator();
        $data = $validator->escape(
            [
            'email' => 'test@univ-amu.fr'
            ]
        );

        $validator->validate($data);

        unset($_SESSION['last_forgot_password_request']);
    }

    // ========================================
    // Tests pour FormValidator (méthodes protégées via classes enfants)
    // ========================================
    #[Test]
    #[DataProvider('phoneNumbersProvider')]
    public function phoneValidationWorksCorrectly(string $phone, bool $shouldBeValid): void
    {
        $validator = new class () extends FormValidator {
            protected $required = [];
            public function validate(array $data): void
            {
            }
            public function testPhone(string $phone): bool
            {
                return $this->isValidPhone($phone);
            }
        };

        $result = $validator->testPhone($phone);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function phoneNumbersProvider(): array
    {
        return [
            'valid_mobile_06' => ['0612345678', true],
            'valid_mobile_07' => ['0712345678', true],
            'valid_landline_04' => ['0412345678', true],
            'invalid_05' => ['0512345678', false],
            'too_short' => ['061234567', false],
            'too_long' => ['06123456789', false],
            'with_spaces' => ['06 12 34 56 78', false],
            'with_dashes' => ['06-12-34-56-78', false],
            'international' => ['+33612345678', false]
        ];
    }

    #[Test]
    #[DataProvider('userTypesProvider')]
    public function userTypeValidationWorksCorrectly(string $type, bool $shouldBeValid): void
    {
        $validator = new class () extends FormValidator {
            protected $required = [];
            public function validate(array $data): void
            {
            }
            public function testUserType(string $type): bool
            {
                return $this->isValidUserType($type);
            }
        };

        $result = $validator->testUserType($type);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function userTypesProvider(): array
    {
        return [
            'student' => ['student', true],
            'professor' => ['professor', true],
            'client' => ['client', true],
            'admin' => ['admin', false],
            'user' => ['user', false],
            'empty' => ['', false],
            'uppercase' => ['STUDENT', false]
        ];
    }

    #[Test]
    #[DataProvider('yearProvider')]
    public function yearValidationWorksCorrectly(string $year, bool $shouldBeValid): void
    {
        $validator = new class () extends FormValidator {
            protected $required = [];
            public function validate(array $data): void
            {
            }
            public function testYear(string $year): bool
            {
                return $this->isValidYear($year);
            }
        };

        $result = $validator->testYear($year);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function yearProvider(): array
    {
        return [
            'year_1' => ['1', true],
            'year_2' => ['2', true],
            'year_3' => ['3', true],
            'year_0' => ['0', false],
            'year_4' => ['4', false],
            'negative' => ['-1', false],
            'text' => ['one', false]
        ];
    }

    #[Test]
    #[DataProvider('tdProvider')]
    public function tdValidationWorksCorrectly(string $td, bool $shouldBeValid): void
    {
        $validator = new class () extends FormValidator {
            protected $required = [];
            public function validate(array $data): void
            {
            }
            public function testTD(string $td): bool
            {
                return $this->isValidTD($td);
            }
        };

        $result = $validator->testTD($td);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function tdProvider(): array
    {
        return [
            'TD1' => ['TD1', true],
            'TD2' => ['TD2', true],
            'TD3' => ['TD3', true],
            'TD4' => ['TD4', true],
            'TD5' => ['TD5', false],
            'td1' => ['td1', false],
            'TD' => ['TD', false]
        ];
    }

    #[Test]
    #[DataProvider('tpProvider')]
    public function tpValidationWorksCorrectly(string $tp, bool $shouldBeValid): void
    {
        $validator = new class () extends FormValidator {
            protected $required = [];
            public function validate(array $data): void
            {
            }
            public function testTP(string $tp): bool
            {
                return $this->isValidTP($tp);
            }
        };

        $result = $validator->testTP($tp);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function tpProvider(): array
    {
        return [
            'TPA' => ['TPA', true],
            'TPB' => ['TPB', true],
            'TPC' => ['TPC', false],
            'tpa' => ['tpa', false],
            'TP' => ['TP', false]
        ];
    }

    // ========================================
    // Tests de sécurité et cas limites
    // ========================================
    #[Test]
    public function escapeMethodHandlesXSSAttempts(): void
    {
        $validator = new LoginValidator();

        $maliciousData = [
            'email' => '<script>alert("XSS")</script>@test.fr',
            'password' => '<img src=x onerror=alert("XSS")>'
        ];

        $escaped = $validator->escape($maliciousData);

        $this->assertStringNotContainsString('<script>', $escaped['email']);
        $this->assertStringNotContainsString('<img', $escaped['password']);
        $this->assertStringContainsString('&lt;', $escaped['email']);
    }

    #[Test]
    public function escapeMethodHandlesSQLInjectionAttempts(): void
    {
        $validator = new LoginValidator();

        $maliciousData = [
            'email' => "admin' OR '1'='1",
            'password' => "' DROP TABLE users--"
        ];

        $escaped = $validator->escape($maliciousData);

        // Les quotes doivent être échappés
        $this->assertStringContainsString('&#039;', $escaped['email']);
        $this->assertStringContainsString('&#039;', $escaped['password']);
    }

    #[Test]
    public function validatorHandlesUnicodeCharacters(): void
    {
        $validator = new ResetPasswordValidator();

        $data = $validator->escape(
            [
            'pwdnew' => 'Pàsswørd123',
            'pwdverif' => 'Pàsswørd123'
            ]
        );

        // Les caractères unicode doivent être préservés
        $this->assertStringContainsString('à', $data['pwdnew']);
        $this->assertStringContainsString('ø', $data['pwdnew']);
    }

    #[Test]
    public function validatorHandlesVeryLongInput(): void
    {
        $validator = new LoginValidator();

        $longEmail = str_repeat('a', 500) . '@test.fr';
        $longPassword = str_repeat('b', 1000);

        $data = $validator->escape(
            [
            'email' => $longEmail,
            'password' => $longPassword
            ]
        );

        $this->assertEquals(strlen($longEmail), strlen($data['email']));
        $this->assertEquals(strlen($longPassword), strlen($data['password']));
    }
}
