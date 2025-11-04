<?php

namespace tests\Unit\Utilis\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Validator\ValidationServiceRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;

/**
 * Unit tests for ValidationServiceRegister
 *
 * @package Tests\Unit\Utilis\Validator
 */
#[CoversClass(ValidationServiceRegister::class)]
#[CoversClass(ExceptionValidationRegisters::class)]
#[CoversClass(ExceptionValidationRegister::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
class ValidationServiceRegisterTest extends TestCase
{
    private ValidationServiceRegister $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidationServiceRegister();
    }

    /**
     * Test valid student registration data
     */
    public function testValidatesCorrectStudentData(): void
    {
        $this->expectNotToPerformAssertions();

        $data = [
            'amu_id' => 'a12345678',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => 'student',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'terms' => 'on',
            'year' => '2',
            'parcours' => 'A',
            'td' => 'TD1',
            'tp' => 'TPA'
        ];

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Test empty required fields throw exception
     */
    public function testThrowsExceptionOnEmptyFields(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $data = [
            'amuId' => '',
            'firstName' => 'Jean'
        ];

        $this->validator->escape($data);
    }

    /**
     * Test invalid email formats
     */
    #[DataProvider('invalidEmailProvider')]
    public function testRejectsInvalidEmails(string $email): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['email'] = $email;
        $data['user_type'] = 'student';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no_at' => ['jeandupont.etu.univ-amu.fr'],
            'wrong_domain' => ['jean.dupont@gmail.com'],
            'missing_name' => ['jean@etu.univ-amu.fr'],
            'spaces' => ['jean dupont@etu.univ-amu.fr'],
            'special_chars' => ['jean$dupont@etu.univ-amu.fr']
        ];
    }

    /**
     * Test password mismatch
     */
    public function testRejectsPasswordMismatch(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['password'] = 'Password123';
        $data['passwordverif'] = 'DifferentPass123';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Test invalid phone numbers
     */
    #[DataProvider('invalidPhoneProvider')]
    public function testRejectsInvalidPhones(string $phone): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['phone'] = $phone;

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            'too_short' => ['061234567'],
            'wrong_prefix' => ['0512345678'],
            'letters' => ['06abcd5678'],
            'international' => ['+33612345678']
        ];
    }

    /**
     * Test student specific validations
     */
    public function testRequiresStudentFieldsForStudents(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['user_type'] = 'student';
        unset($data['year']); // Missing required field

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Test TD4 not allowed for BUT 2/3
     */
    public function testRejectsTD4ForYearTwoAndThree(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['user_type'] = 'student';
        $data['year'] = '2';
        $data['parcours'] = 'A';
        $data['td'] = 'TD4';
        $data['tp'] = 'TPA';

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Test parcours required for BUT 2/3
     */
    public function testRequiresParcoursForYearTwoAndThree(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $data = $this->getValidBaseData();
        $data['user_type'] = 'student';
        $data['year'] = '2';
        $data['td'] = 'TD1';
        $data['tp'] = 'TPA';
        // Missing parcours

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Test professor doesn't need student fields
     */
    public function testProfessorDoesNotNeedStudentFields(): void
    {
        $this->expectNotToPerformAssertions();

        $data = $this->getValidBaseData();
        $data['user_type'] = 'professor';
        // No student fields

        $escaped = $this->validator->escape($data);
        $this->validator->validate($escaped);
    }

    /**
     * Helper to get valid base data
     */
    private function getValidBaseData(): array
    {
        return [
            'amu_id' => 'test123',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => 'professor',
            'email' => 'jean.dupont@univ-amu.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'terms' => 'on'
        ];
    }
}
