<?php

namespace Tests\Unit\Domain\User;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Domain\User\User;
use App\Domain\User\Student;
use App\Domain\User\Professor;
use App\Domain\User\Client;

/**
 * Tests for the factory methods of User
 */
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
class UserFactoryMethodsTest extends TestCase
{
    // ========================================
    // Tests for createFromRegistrationData
    // ========================================
 
    #[Test]
    public function createFromRegistrationDataCreatesStudentCorrectly(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => 'SecurePass123',
            'amu_id' => 'dupont123',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA',
            'major' => 'A'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('Jean', $user->getFirstName());
        $this->assertEquals('Dupont', $user->getLastName());
        $this->assertEquals('jean.dupont@etu.univ-amu.fr', $user->getEmail());
        $this->assertTrue($user->isStudent());
    }

    #[Test]
    public function createFromRegistrationDataCreatesProfessorCorrectly(): void
    {
        $data = [
            'user_type' => 'professor',
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@univ-amu.fr',
            'phone' => '0623456789',
            'password' => 'ProfPass123',
            'amu_id' => 'martin456'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf(Professor::class, $user);
        $this->assertEquals('Marie', $user->getFirstName());
        $this->assertEquals('Martin', $user->getLastName());
        $this->assertTrue($user->isProfessor());
    }

    #[Test]
    public function createFromRegistrationDataCreatesClientCorrectly(): void
    {
        $data = [
            'user_type' => 'client',
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'email' => 'pierre.durand@company.com',
            'phone' => '0634567890',
            'password' => 'ClientPass123',
            'organisation' => 'Tech Corp'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf(Client::class, $user);
        $this->assertEquals('Pierre', $user->getFirstName());
        $this->assertTrue($user->isClient());
    }

    #[Test]
    public function createFromRegistrationDataHashesPassword(): void
    {
        $plainPassword = 'MySecretPassword123';
        $data = [
            'user_type' => 'student',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => $plainPassword,
            'amu_id' => 'test123',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ];

        $user = User::createFromRegistrationData($data);
        $hash = $user->getPasswordHash();

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($plainPassword, $hash);
        $this->assertTrue(password_verify($plainPassword, $hash));
    }

    #[Test]
    public function createFromRegistrationDataThrowsExceptionForInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Type d'utilisateur invalide");

        User::createFromRegistrationData(
            [
                'user_type' => 'invalid_type',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@test.fr',
                'phone' => '0612345678',
                'password' => 'password123'
            ]
        );
    }

    #[Test]
    #[DataProvider('invalidUserTypesProvider')]
    public function createFromRegistrationDataRejectsInvalidTypes(string $invalidType): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::createFromRegistrationData(
            [
                'user_type' => $invalidType,
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@test.fr',
                'phone' => '0612345678',
                'password' => 'password123'
            ]
        );
    }

    public static function invalidUserTypesProvider(): array
    {
        return [
            'empty' => [''],
            'admin' => ['admin'],
            'user' => ['user'],
            'uppercase' => ['STUDENT'],
            'number' => ['1'],
            'special_chars' => ['student!']
        ];
    }
}