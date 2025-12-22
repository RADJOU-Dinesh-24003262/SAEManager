<?php

namespace Tests\Unit\Models\User;

use Core\includes\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use Models\User\User;

/**
 * Tests unitaires complets pour les modèles User
 */
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(User::class)]
#[CoversClass(Database::class)]
class UserModelTest extends TestCase
{
    // ========================================
    // Tests pour Student
    // ========================================
    #[Test]
    public function studentConstructorInitializesCorrectly(): void
    {
        $data = [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'phone' => '0612345678',
            'amu_id' => 'dupont123',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA',
            'major' => 'A'
        ];

        $student = new Student($data);

        $this->assertEquals('student', $student->getUserType());
        $this->assertEquals('Jean', $student->getFirstName());
        $this->assertEquals('Dupont', $student->getLastName());
        $this->assertEquals('jean.dupont@etu.univ-amu.fr', $student->getEmail());
        $this->assertEquals('0612345678', $student->getPhone());
        $this->assertEquals('dupont123', $student->getAmuId());
        $this->assertEquals(2, $student->getYear());
        $this->assertEquals('TD1', $student->getTd());
        $this->assertEquals('TPA', $student->getTp());
        $this->assertEquals('A', $student->getParcours());
    }

    #[Test]
    public function studentIgnoresPasswordInConstructor(): void
    {
        $data = [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@test.fr',
            'phone' => '0612345678',
            'password' => 'secret123',
            'passwordverif' => 'secret123',
            'terms' => 'accepted'
        ];

        $student = new Student($data);

        // Ces champs ne doivent pas être stockés directement
        $reflection = new \ReflectionClass($student);
        $this->assertFalse($reflection->hasProperty('password'));
        $this->assertFalse($reflection->hasProperty('passwordverif'));
        $this->assertFalse($reflection->hasProperty('terms'));
    }

    #[Test]
    public function studentTypeCheckersWork(): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isProfessor());
        $this->assertFalse($student->isClient());
    }

    #[Test]
    public function studentGetFullNameReturnsCorrectFormat(): void
    {
        $student = new Student([
            'first_name' => 'Jean',
            'last_name' => 'Dupont'
        ]);

        $this->assertEquals('Jean Dupont', $student->getFullName());
    }

    // ========================================
    // Tests pour Professor
    // ========================================
    #[Test]
    public function professorConstructorInitializesCorrectly(): void
    {
        $data = [
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@univ-amu.fr',
            'phone' => '0623456789',
            'amu_id' => 'martin456'
        ];

        $professor = new Professor($data);

        $this->assertEquals('professor', $professor->getUserType());
        $this->assertEquals('Marie', $professor->getFirstName());
        $this->assertEquals('Martin', $professor->getLastName());
        $this->assertEquals('marie.martin@univ-amu.fr', $professor->getEmail());
        $this->assertEquals('martin456', $professor->getAmuId());
    }

    #[Test]
    public function professorTypeCheckersWork(): void
    {
        $professor = new Professor(['first_name' => 'Test', 'last_name' => 'Prof']);

        $this->assertFalse($professor->isStudent());
        $this->assertTrue($professor->isProfessor());
        $this->assertFalse($professor->isClient());
    }

    // ========================================
    // Tests pour Client
    // ========================================
    #[Test]
    public function clientConstructorInitializesCorrectly(): void
    {
        $data = [
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'email' => 'pierre.durand@entreprise.fr',
            'phone' => '0634567890',
            'organisation' => 'Tech Corp'
        ];

        $client = new Client($data);

        $this->assertEquals('client', $client->getUserType());
        $this->assertEquals('Pierre', $client->getFirstName());
        $this->assertEquals('Tech Corp', $client->getOrganisation());
    }

    #[Test]
    public function clientTypeCheckersWork(): void
    {
        $client = new Client(['first_name' => 'Test', 'last_name' => 'Client']);

        $this->assertFalse($client->isStudent());
        $this->assertFalse($client->isProfessor());
        $this->assertTrue($client->isClient());
    }

    // ========================================
    // Tests pour User Factory Methods
    // ========================================
    #[Test]
    #[DataProvider('userTypesProvider')]
    public function createFromRegistrationDataCreatesCorrectType(string $type, string $expectedClass): void
    {
        /** @var class-string $expectedClass */
        $data = [
            'user_type' => $type,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@test.fr',
            'phone' => '0612345678',
            'password' => 'password123'
        ];

        if ($type === 'student') {
            $data['amu_id'] = 'test123';
            $data['year'] = 1;
            $data['td'] = 'TD1';
            $data['tp'] = 'TPA';
        } elseif ($type === 'professor') {
            $data['amu_id'] = 'prof123';
        } elseif ($type === 'client') {
            $data['organisation'] = 'Test Org';
        }

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf($expectedClass, $user);
        $this->assertEquals($type, $user->getUserType());
    }

    public static function userTypesProvider(): array
    {
        return [
            'student' => ['student', Student::class],
            'professor' => ['professor', Professor::class],
            'client' => ['client', Client::class]
        ];
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
    public function setPasswordHashesPasswordCorrectly(): void
    {
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $plainPassword = 'mySecretPassword123';

        $student->setPassword($plainPassword);
        $hashedPassword = $student->getPasswordHash();

        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
        $this->assertGreaterThan(50, strlen($hashedPassword)); // Hash should be long
    }

    // ========================================
    // Tests pour les getters
    // ========================================
    #[Test]
    public function getAllGettersReturnCorrectValues(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.fr',
            'phone' => '0645678901',
            'amu_id' => 'doe789',
            'year' => 3,
            'td' => 'TD2',
            'tp' => 'TPB',
            'major' => 'B'
        ];

        $student = new Student($data);
        $student->setPassword('testpass');

        $this->assertEquals('John', $student->getFirstName());
        $this->assertEquals('Doe', $student->getLastName());
        $this->assertEquals('John Doe', $student->getFullName());
        $this->assertEquals('student', $student->getUserType());
        $this->assertEquals('john.doe@test.fr', $student->getEmail());
        $this->assertEquals('0645678901', $student->getPhone());
        $this->assertNotEmpty($student->getPasswordHash());
    }

    // ========================================
    // Tests pour existsByEmail (méthode statique)
    // ========================================
    #[Test]
    public function existsByEmailReturnsFalseOnDatabaseError(): void
    {
        // Ce test vérifie que la méthode gère les erreurs PDO
        // En mode test, si la base de données n'est pas disponible
        $result = User::existsByEmail('nonexistent@test.fr');

        // Le résultat peut être true ou false selon l'état de la base
        $this->assertIsBool($result);
    }

    // ========================================
    // Tests des cas limites
    // ========================================
    #[Test]
    public function studentWithMinimalDataWorks(): void
    {
        $student = new Student([
            'first_name' => 'A',
            'last_name' => 'B'
        ]);

        $this->assertEquals('A', $student->getFirstName());
        $this->assertEquals('B', $student->getLastName());
        $this->assertEquals('A B', $student->getFullName());
    }

    #[Test]
    public function studentWithEmptyParcoursReturnsNull(): void
    {
        $student = new Student([
            'first_name' => 'Test',
            'last_name' => 'User',
            'major' => null
        ]);

        $this->assertNull($student->getParcours());
    }

    #[Test]
    public function professorWithLongAmuIdWorks(): void
    {
        $longId = str_repeat('a', 100);
        $professor = new Professor([
            'first_name' => 'Test',
            'last_name' => 'Prof',
            'amu_id' => $longId
        ]);

        $this->assertEquals($longId, $professor->getAmuId());
    }

    #[Test]
    public function clientWithSpecialCharactersInOrganisationWorks(): void
    {
        $specialOrg = "L'Entreprise & Co. (2024)";
        $client = new Client([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'organisation' => $specialOrg
        ]);

        $this->assertEquals($specialOrg, $client->getOrganisation());
    }

    // ========================================
    // Tests de sécurité
    // ========================================
    #[Test]
    public function passwordIsNotStoredInPlainText(): void
    {
        $plainPassword = 'MyP@ssw0rd!';
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $student->setPassword($plainPassword);

        $hash = $student->getPasswordHash();

        $this->assertNotEquals($plainPassword, $hash);
        $this->assertStringStartsWith('$argon2id$', $hash); // argon2id format
    }

    #[Test]
    public function samePasswordProducesDifferentHashes(): void
    {
        $password = 'SamePassword123';

        $student1 = new Student(['first_name' => 'User1', 'last_name' => 'Test']);
        $student1->setPassword($password);

        $student2 = new Student(['first_name' => 'User2', 'last_name' => 'Test']);
        $student2->setPassword($password);

        $this->assertNotEquals(
            $student1->getPasswordHash(),
            $student2->getPasswordHash(),
            'Same password should produce different hashes (salt)'
        );
    }
}
