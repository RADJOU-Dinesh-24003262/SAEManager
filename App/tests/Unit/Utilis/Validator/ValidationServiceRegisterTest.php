<?php

namespace Tests\Unit\Utilis\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Validator\ValidationServiceRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;

/**
 * Unit tests for ValidationServiceRegister.
 * * This test suite ensures that user registration data is correctly sanitized and validated
 * according to business rules, specifically handling different email domains for
 * students (etu.univ-amu.fr) and professors (univ-amu.fr).
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

    /**
     * Set up the test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidationServiceRegister();
    }

    /**
     * Data provider for invalid emails.
     */
    public static function invalidEmailProvider(): array
    {
        return [
            'wrong_domain_for_student' => ['jean.dupont@univ-amu.fr'], // Prof domain used for student
            'external_domain' => ['jean.dupont@gmail.com'],
            'mismatched_prefix' => ['pierre.martin@etu.univ-amu.fr'], // Names in data are Jean Dupont
            'missing_parts' => ['jean@etu.univ-amu.fr'],
            'spaces' => ['jean dupont@etu.univ-amu.fr']
        ];
    }


    public static function invalidPhoneProvider(): array
    {
        return [
            'too_short' => ['061234567'],
            'wrong_landline_prefix' => ['0112345678'], // Valid French prefixes are 04, 06, 07
            'contains_letters' => ['06ab123456'],
            'international_format' => ['+33612345678']
        ];
    }


    /**
     * Helper method to generate valid base data for different user types.
     * * @param string $type The user type ('student' or 'professor').
     * @return array The data array.
     */
    private function getValidBaseData(string $type): array
    {
        $domain = ($type === 'student') ? '@etu.univ-amu.fr' : '@univ-amu.fr';

        $base = [
            'amu_id' => 't12343305',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'user_type' => $type,
            'email' => 'jean.dupont' . $domain,
            'password' => 'SecurePass123',
            'passwordverif' => 'SecurePass123',
            'phone' => '0612345678',
            'terms' => 'on'
        ];

        // Add default student fields if needed to avoid basic validation errors
        if ($type === 'student') {
            $base['year'] = '1';
            $base['td'] = 'TD1';
            $base['tp'] = 'TPA';
        }

        return $base;
    }
}
