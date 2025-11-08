<?php

namespace Tests\Unit\Validator\Registration;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Validator\Registration\StudentRegistrationValidator;
use Validator\Registration\ProfessorRegistrationValidator;
use Validator\Registration\ClientRegistrationValidator;
use Validator\Registration\RegistrationValidatorFactory;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;

/**
 * Unit tests for the refactored registration validators
 */
#[CoversClass(StudentRegistrationValidator::class)]
#[CoversClass(ProfessorRegistrationValidator::class)]
#[CoversClass(ClientRegistrationValidator::class)]
#[CoversClass(RegistrationValidatorFactory::class)]
#[CoversClass(ExceptionValidationRegister::class)]
#[CoversClass(ExceptionValidationRegisters::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
class RegistrationValidatorTest extends TestCase
{
    /**
     * Test factory creates correct validator for each user type
     * @param string       $userType
     * @param class-string $expectedClass
     * @return void
     */
    #[DataProvider('userTypeProvider')]
    public function testFactoryCreatesCorrectValidator(
        string $userType,
        string $expectedClass
    ): void {
        $validator = RegistrationValidatorFactory::create($userType);
        $this->assertInstanceOf($expectedClass, $validator);
    }

    /**
     * Provides user types and expected validator classes
     * @return array<mixed>
     */
    public static function userTypeProvider(): array
    {
        return [
            'student' => ['student', StudentRegistrationValidator::class],
            'professor' => ['professor', ProfessorRegistrationValidator::class],
            'client' => ['client', ClientRegistrationValidator::class],
        ];
    }

    /**
     * Test factory throws exception for invalid user type
     */
    public function testFactoryThrowsExceptionForInvalidUserType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RegistrationValidatorFactory::create('invalid_type');
    }

    /**
     * Test student validator validates correct data
     */
    public function testStudentValidatorAcceptsValidData(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new StudentRegistrationValidator();
        $data = [
            'amu_id' => 'a12345678',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => 'student',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'year' => '2',
            'parcours' => 'A',
            'td' => 'TD1',
            'tp' => 'TPA',
            'terms' => 'on'
        ];

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Test student validator rejects missing year
     */
    public function testStudentValidatorRejectsMissingYear(): void
    {
        $this->expectException(ExceptionValidationEmptys::class);

        $validator = new StudentRegistrationValidator();
        $data = $this->getBaseStudentData();
        unset($data['year']);

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Test student validator rejects TD4 for BUT 2/3
     */
    public function testStudentValidatorRejectsTD4ForYearTwoThree(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $validator = new StudentRegistrationValidator();
        $data = $this->getBaseStudentData();
        $data['year'] = '2';
        $data['parcours'] = 'A';
        $data['td'] = 'TD4';

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Test professor validator validates correct data
     */
    public function testProfessorValidatorAcceptsValidData(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ProfessorRegistrationValidator();
        $data = [
            'amu_id' => 'p12345678',
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'user_type' => 'professor',
            'email' => 'marie.martin@univ-amu.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'terms' => 'on'
        ];

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Test client validator validates correct data
     */
    public function testClientValidatorAcceptsValidData(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ClientRegistrationValidator();
        $data = [
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'user_type' => 'client',
            'email' => 'pierre.durand@company.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'organisation' => 'TechCorp',
            'terms' => 'on'
        ];

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Test client validator with small organisation name
     */
    public function testClientValidatorRejectsAmuEmail(): void
    {
        $this->expectException(ExceptionValidationRegisters::class);

        $validator = new ClientRegistrationValidator();
        $data = [
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'user_type' => 'client',
            'email' => 'pierre.durand@univ-amu.fr', // AMU email!
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'organisation' => 'T',
            'terms' => 'on'
        ];

        $escaped = $validator->escape($data);
        $validator->validate($escaped);
    }

    /**
     * Helper method to get base student data
     * @return array<string, string>
     */
    private function getBaseStudentData(): array
    {
        return [
            'amu_id' => 'a12345678',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => 'student',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'year' => '1',
            'td' => 'TD1',
            'tp' => 'TPA',
            'terms' => 'on'
        ];
    }
}
