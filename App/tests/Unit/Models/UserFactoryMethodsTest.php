<?php

namespace Tests\Unit\Models\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Models\User\User;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use Core\includes\Database;

/**
 * Tests for the factory methods of User
 */
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(Database::class)]
class UserFactoryMethodsTest extends TestCase
{
    // ========================================
    // Tests for createFromRegistrationData
    // ========================================
    protected function setUp(): void
    {
        parent::setUp();
        // Crée une instance Database en mémoire et l'injecte
        $db = new \Core\includes\Database();
        \Core\includes\Database::setInstance($db);
    }
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type d'utilisateur invalide");

        User::createFromRegistrationData([
            'user_type' => 'invalid_type',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@test.fr',
            'phone' => '0612345678',
            'password' => 'password123'
        ]);
    }

    #[Test]
    #[DataProvider('invalidUserTypesProvider')]
    public function createFromRegistrationDataRejectsInvalidTypes(string $invalidType): void
    {
        $this->expectException(\InvalidArgumentException::class);

        User::createFromRegistrationData([
            'user_type' => $invalidType,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@test.fr',
            'phone' => '0612345678',
            'password' => 'password123'
        ]);
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

    // ========================================
    // Tests for existsByEmail
    // ========================================

    #[Test]
    public function existsByEmailReturnsBooleanValue(): void
    {
        $result = User::existsByEmail('test@univ-amu.fr');
        
        $this->assertIsBool($result);
    }

    #[Test]
    public function existsByEmailHandlesEmptyEmail(): void
    {
        $result = User::existsByEmail('');
        
        $this->assertIsBool($result);
        // In General, should return false for empty email
        $this->assertFalse($result);
    }

    #[Test]
    #[DataProvider('emailFormatsProvider')]
    public function existsByEmailHandlesDifferentEmailFormats(string $email): void
    {
        $result = User::existsByEmail($email);
        
        $this->assertIsBool($result);
    }

    public static function emailFormatsProvider(): array
    {
        return [
            'student_email' => ['jean.dupont@etu.univ-amu.fr'],
            'professor_email' => ['prof@univ-amu.fr'],
            'with_number' => ['jean.dupont.1@etu.univ-amu.fr'],
            'long_email' => ['very.long.email.address@etu.univ-amu.fr'],
            'invalid_email' => ['not-an-email']
        ];
    }

    // ========================================
    // Tests for setPassword
    // ========================================

    #[Test]
    public function setPasswordHashesCorrectly(): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $plainPassword = 'MyPassword123';

        $student->setPassword($plainPassword);
        $hash = $student->getPasswordHash();

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($plainPassword, $hash);
        $this->assertTrue(password_verify($plainPassword, $hash));
        $this->assertStringStartsWith('$2y$', $hash); // bcrypt format
    }

    #[Test]
    public function setPasswordCreatesDifferentHashesForSamePassword(): void
    {
        $student1 = new Student(['first_name' => 'User1', 'last_name' => 'Test']);
        $student2 = new Student(['first_name' => 'User2', 'last_name' => 'Test']);
        $password = 'SamePassword123';

        $student1->setPassword($password);
        $student2->setPassword($password);

        $hash1 = $student1->getPasswordHash();
        $hash2 = $student2->getPasswordHash();

        $this->assertNotEquals($hash1, $hash2, 'Same password should produce different hashes due to random salt');
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    #[Test]
    #[DataProvider('passwordsProvider')]
    public function setPasswordHandlesDifferentPasswordFormats(string $password): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        
        $student->setPassword($password);
        $hash = $student->getPasswordHash();

        $this->assertNotEmpty($hash);
        $this->assertTrue(password_verify($password, $hash));
    }

    public static function passwordsProvider(): array
    {
        return [
            'short' => ['12345678'],
            'long' => ['ThisIsAVeryLongPasswordWithMoreThan50Characters123456789'],
            'with_special' => ['P@ssw0rd!#$%'],
            'with_spaces' => ['My Pass Word 123'],
            'unicode' => ['Pàsswørd€123'],
            'only_numbers' => ['12345678'],
            'only_letters' => ['abcdefgh']
        ];
    }

    // ========================================
    // Tests for getFullName
    // ========================================

    #[Test]
    public function getFullNameReturnsCorrectFormat(): void
    {
        $student = new Student([
            'first_name' => 'Jean',
            'last_name' => 'Dupont'
        ]);

        $this->assertEquals('Jean Dupont', $student->getFullName());
    }

    #[Test]
    #[DataProvider('nameFormatsProvider')]
    public function getFullNameHandlesDifferentNameFormats(string $firstName, string $lastName, string $expected): void
    {
        $student = new Student([
            'first_name' => $firstName,
            'last_name' => $lastName
        ]);

        $this->assertEquals($expected, $student->getFullName());
    }

    public static function nameFormatsProvider(): array
    {
        return [
            'simple' => ['Jean', 'Dupont', 'Jean Dupont'],
            'with_hyphen' => ['Jean-Pierre', 'Dupont-Martin', 'Jean-Pierre Dupont-Martin'],
            'with_spaces' => ['Jean Paul', 'De La Fontaine', 'Jean Paul De La Fontaine'],
            'single_char' => ['A', 'B', 'A B'],
            'unicode' => ['François', 'Müller', 'François Müller']
        ];
    }

    // ========================================
    // Tests for the type checkers
    // ========================================

    #[Test]
    public function typeCheckersWorkCorrectlyForStudent(): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isProfessor());
        $this->assertFalse($student->isClient());
    }

    #[Test]
    public function typeCheckersWorkCorrectlyForProfessor(): void
    {
        $professor = new Professor(['first_name' => 'Test', 'last_name' => 'Prof']);

        $this->assertFalse($professor->isStudent());
        $this->assertTrue($professor->isProfessor());
        $this->assertFalse($professor->isClient());
    }

    #[Test]
    public function typeCheckersWorkCorrectlyForClient(): void
    {
        $client = new Client(['first_name' => 'Test', 'last_name' => 'Client']);

        $this->assertFalse($client->isStudent());
        $this->assertFalse($client->isProfessor());
        $this->assertTrue($client->isClient());
    }

    // ========================================
    // Security related tests
    // ========================================

    #[Test]
    public function passwordFieldsAreNotStoredInConstructor(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'password' => 'should_not_be_stored',
            'passwordverif' => 'also_not_stored',
            'terms' => 'ignored'
        ];

        $student = new Student($data);

        // Checks that these fields are not accessible as properties
        $reflection = new \ReflectionClass($student);
        $this->assertFalse($reflection->hasProperty('password'));
        $this->assertFalse($reflection->hasProperty('passwordverif'));
        $this->assertFalse($reflection->hasProperty('terms'));
    }

    #[Test]
    public function userTypeCaseIsConsistent(): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $professor = new Professor(['first_name' => 'Test', 'last_name' => 'Prof']);
        $client = new Client(['first_name' => 'Test', 'last_name' => 'Client']);

        $this->assertEquals('student', $student->getUserType());
        $this->assertEquals('professor', $professor->getUserType());
        $this->assertEquals('client', $client->getUserType());
    }
}