<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Models\User\Student;
use Models\User\User;

/**
 * Unit tests for Student model
 *
 * @category Test
 * @package  Tests\Unit\Models
 */
#[CoversClass(Student::class)]
#[CoversClass(User::class)]
class StudentModelTest extends TestCase
{
    #[Test]
    public function studentCanBeInstantiatedWithValidData(): void
    {
        $data = [
            'amu_id' => 'test123',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'phone' => '0612345678',
            'year' => 2,
            'major' => 'A',
            'td' => 'TD1',
            'tp' => 'TPA'
        ];

        $student = new Student($data);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Jean', $student->getFirstName());
        $this->assertEquals('Dupont', $student->getLastName());
        $this->assertEquals('jean.dupont@etu.univ-amu.fr', $student->getEmail());
    }

    #[Test]
    public function studentHasCorrectUserType(): void
    {
        $student = new Student(['first_name' => 'Test']);

        $this->assertEquals('student', $student->getUserType());
        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isProfessor());
        $this->assertFalse($student->isClient());
    }

    #[Test]
    public function studentGettersReturnCorrectValues(): void
    {
        $data = [
            'amu_id' => 'test456',
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@etu.univ-amu.fr',
            'phone' => '0623456789',
            'year' => 3,
            'major' => 'B',
            'td' => 'TD2',
            'tp' => 'TPB'
        ];

        $student = new Student($data);

        $this->assertEquals('test456', $student->getAmuId());
        $this->assertEquals(3, $student->getYear());
        $this->assertEquals('B', $student->getMajor());
        $this->assertEquals('TD2', $student->getTd());
        $this->assertEquals('TPB', $student->getTp());
        $this->assertEquals('Marie Martin', $student->getFullName());
    }

    #[Test]
    public function passwordCanBeSetAndHashed(): void
    {
        $student = new Student(['first_name' => 'Test']);
        $plainPassword = 'SecurePassword123';

        $student->setPassword($plainPassword);
        $hashedPassword = $student->getPasswordHash();

        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    #[Test]
    public function studentIgnoresInvalidProperties(): void
    {
        $data = [
            'first_name' => 'Test',
            'invalid_property' => 'should be ignored',
            'another_invalid' => 123
        ];

        $student = new Student($data);

        $this->assertEquals('Test', $student->getFirstName());
        // Should not throw any errors
        $this->assertInstanceOf(Student::class, $student);
    }

    #[Test]
    public function studentIgnoresPasswordFieldsInConstructor(): void
    {
        $data = [
            'first_name' => 'Test',
            'password' => 'should_be_ignored',
            'passwordverif' => 'also_ignored',
            'terms' => 'ignored_too'
        ];

        $student = new Student($data);

        // These fields should be filtered out
        $this->assertInstanceOf(Student::class, $student);
    }

    public static function validStudentDataProvider(): array
    {
        return [
            'first_year_student' => [[
                'amu_id' => 'std001',
                'first_name' => 'Alice',
                'last_name' => 'Bernard',
                'year' => 1,
                'td' => 'TD1',
                'tp' => 'TPA'
            ]],
            'second_year_with_major' => [[
                'amu_id' => 'std002',
                'first_name' => 'Bob',
                'last_name' => 'Charles',
                'year' => 2,
                'major' => 'A',
                'td' => 'TD2',
                'tp' => 'TPB'
            ]],
            'third_year_with_major' => [[
                'amu_id' => 'std003',
                'first_name' => 'Charlie',
                'last_name' => 'David',
                'year' => 3,
                'major' => 'B',
                'td' => 'TD3',
                'tp' => 'TPA'
            ]]
        ];
    }

    #[Test]
    #[DataProvider('validStudentDataProvider')]
    public function studentCanBeCreatedWithVariousValidData(array $data): void
    {
        $student = new Student($data);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals($data['first_name'], $student->getFirstName());
        $this->assertEquals($data['last_name'], $student->getLastName());
        $this->assertEquals($data['year'], $student->getYear());
    }

    #[Test]
    public function nullMajorIsHandledCorrectly(): void
    {
        $data = [
            'first_name' => 'Test',
            'year' => 1,
            'major' => null
        ];

        $student = new Student($data);

        $this->assertNull($student->getMajor());
    }
}
