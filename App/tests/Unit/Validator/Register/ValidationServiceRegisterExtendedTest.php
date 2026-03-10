<?php

namespace Tests\Unit\Validator\Register;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Validator\Register\ValidationServiceRegister;
use Validator\FormValidator;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationRegisters;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationRegister;

/**
 * Extended unit tests for ValidationServiceRegister
 */
#[CoversClass(ValidationServiceRegister::class)]
#[CoversClass(FormValidator::class)]
#[CoversClass(ExceptionValidationRegisters::class)]
#[CoversClass(ExceptionValidationRegister::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
class ValidationServiceRegisterExtendedTest extends TestCase
{
    private ValidationServiceRegister $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidationServiceRegister();
    }

    // Tests for email validation
    #[Test]
    public function validAmuEmailIsAccepted(): void
    {
        $this->expectNotToPerformAssertions();

        $data = $this->getValidStudentData();
        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    // Tests for password validation
    public static function invalidPasswordsProvider(): array
    {
        return [
            'too_short' => ['1234567'],
            'seven_chars' => ['abcdefg']
        ];
    }

    #[Test]
    #[DataProvider('invalidPasswordsProvider')]
    public function shortPasswordsAreRejected(string $password): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['password'] = $password;
        $data['passwordverif'] = $password;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function passwordMismatchIsDetected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['password'] = 'ValidP@ss1234!';
        $data['passwordverif'] = 'DiffP@ss5678!';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    // Tests for phone validation
    public static function invalidPhoneNumbersProvider(): array
    {
        return [
            'too_short' => ['061234567'],
            'too_long' => ['06123456789'],
            'wrong_prefix' => ['0512345678'],
            'with_letters' => ['06abcd5678'],
            'international' => ['+33612345678'],
            'with_spaces' => ['06 12 34 56 78'],
            'with_dashes' => ['06-12-34-56-78']
        ];
    }

    #[Test]
    #[DataProvider('invalidPhoneNumbersProvider')]
    public function invalidPhoneNumbersAreRejected(string $phone): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['phone'] = $phone;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function validPhoneNumbersProvider(): array
    {
        return [
            'mobile_06' => ['0612345678'],
            'mobile_07' => ['0712345678'],
            'landline_04' => ['0412345678']
        ];
    }

    #[Test]
    #[DataProvider('validPhoneNumbersProvider')]
    public function validPhoneNumbersAreAccepted(string $phone): void
    {
        $this->expectNotToPerformAssertions();

        $data = $this->getValidStudentData();
        $data['phone'] = $phone;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    // Tests for student-specific fields
    #[Test]
    public function studentWithoutYearIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        unset($data['year']);

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function studentWithoutTdIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        unset($data['td']);

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function studentWithoutTpIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        unset($data['tp']);

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function year2StudentWithoutMajorIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = '2';
        unset($data['major']);

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function year3StudentWithoutMajorIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = '3';
        unset($data['major']);

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function year2StudentWithTD4IsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = '2';
        $data['major'] = 'A';
        $data['td'] = 'TD4';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function year1StudentWithMajorIsRejected(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = '1';
        $data['major'] = 'A';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidYearsProvider(): array
    {
        return [
            'zero' => ['0'],
            'four' => ['4'],
            'negative' => ['-1'],
            'letter' => ['A'],
            'decimal' => ['1.5']
        ];
    }

    #[Test]
    #[DataProvider('invalidYearsProvider')]
    public function invalidYearsAreRejected(string $year): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = $year;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidMajorProvider(): array
    {
        return [
            'lowercase' => ['a'],
            'number' => ['1'],
            'c' => ['C'],
            'empty' => ['']
        ];
    }

    #[Test]
    #[DataProvider('invalidMajorProvider')]
    public function invalidMajorsAreRejected(string $major): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['year'] = '2';
        $data['major'] = $major;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    // Tests for professor and client
    #[Test]
    public function professorDoesNotNeedStudentFields(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'amu_id' => 'p12343305',
            'first_name' => 'Prof',
            'last_name' => 'Dupont',
            'user_type' => 'professor',
            'email' => 'prof.dupont',
            'password' => 'SecureP@ss2026!',
            'passwordverif' => 'SecureP@ss2026!',
            'phone' => '0612345678',
            'terms' => 'on',
            'h-captcha-response' => 'test-captcha-success'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    #[Test]
    public function clientDoesNotNeedStudentFields(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'amu_id' => 'client123',
            'first_name' => 'Client',
            'last_name' => 'Martin',
            'user_type' => 'client',
            'email' => 'client.martin@univ-amu.fr',
            'password' => 'SecureP@ss2026!',
            'passwordverif' => 'SecureP@ss2026!',
            'phone' => '0612345678',
            'organisation' => 'Ma Société',
            'terms' => 'on',
            'h-captcha-response' => 'test-captcha-success'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidUserTypesProvider(): array
    {
        return [
            'admin' => ['admin'],
            'user' => ['user'],
            'lowercase_student' => ['STUDENT'],
            'number' => ['1']
        ];
    }

    #[Test]
    #[DataProvider('invalidUserTypesProvider')]
    public function invalidUserTypesAreRejected(string $userType): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidStudentData();
        $data['user_type'] = $userType;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    // Tests for escape method
    #[Test]
    public function escapeMethodSanitizesHtmlCharacters(): void
    {
        $data = [
            'amu_id' => 'test<script>',
                'first_name' => 'Jean<b>Bold</b>',
                    'last_name' => 'Dupont',
                        'user_type' => 'student',
                            'email' => 'jean.dupont@etu.univ-amu.fr',
                                'password' => 'SecureP@ss2026!',
                                    'passwordverif' => 'SecureP@ss2026!',
                                        'phone' => '0612345678',
                                            'year' => '1',
                                                'td' => 'TD1',
                                                    'tp' => 'TPA',
                                                        'terms' => 'on',
                                                            'h-captcha-response' => 'test-captcha-success'
        ];

                $escaped = $this -> validator -> escape($data);
                $this -> assertStringContainsString('&lt;script&gt;', $escaped['amu_id']);
                $this -> assertStringContainsString('&lt;b&gt;', $escaped['first_name']);
    }

    #[Test]
    public function missingRequiredFieldThrowsException(): void
    {
        $this -> expectException(ExceptionValidationEmptys:: class);

        $data = [
            'amu_id' => '',
            'first_name' => 'Jean'
        ];

        $this -> validator -> escape($data);
    }

                // Helper methods
    private function getValidStudentData(): array
    {
        return [
            'amu_id' => 't12333305',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => 'student',
            'email' => 'jean.dupont',
            'password' => 'SecureP@ss2026!',
            'passwordverif' => 'SecureP@ss2026!',
            'phone' => '0612345678',
            'year' => '1',
            'td' => 'TD1',
            'tp' => 'TPA',
            'terms' => 'on',
            'h-captcha-response' => 'test-captcha-success'
        ];
    }
}
