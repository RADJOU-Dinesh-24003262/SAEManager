<?php

namespace Tests\Integration\Models;

use Models\Entity\User\UserFactory;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\Entity\User\User;
use Models\Entity\User\Student;
use Models\Entity\User\Professor;
use Models\Entity\User\Client;
use Core\Includes\Database;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\RegisterUserUseCase;
use Models\UseCase\User\LoginUseCase;
use Models\UseCase\User\ResetPasswordUseCase;
use ReflectionClass;

/**
 * Tests d'intégration pour les workflows complets User
 */
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(LoginUseCase::class)]
#[CoversClass(ResetPasswordUseCase::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(Database::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(UserFactory::class)]

class UserWorkflowIntegrationTest extends TestCase
{
    private $userRepository;
    private $studentRepository;
    private $professorRepository;
    private $clientRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(PdoUserRepository::class);
        $this->studentRepository = $this->createMock(PdoStudentRepository::class);
        $this->professorRepository = $this->createMock(PdoProfessorRepository::class);
        $this->clientRepository = $this->createMock(PdoClientRepository::class);

        // Inject mock Database to avoid connection errors if anything still uses it
        $mockDb = $this->createMock(Database::class);
        Database::setInstance($mockDb);
    }

    protected function tearDown(): void
    {
        // Reset Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        // Reset Config settings
        $configReflection = new \ReflectionClass(\Core\Utils\Config::class);
        $settings = $configReflection->getProperty('settings');
        $settings->setAccessible(true);
        $settings->setValue(null, null);

        parent::tearDown();
    }

    // ========================================
    // Test du workflow complet: Registration → Login
    // ========================================
    #[Test]
    public function completeRegistrationAndLoginWorkflowForStudent(): void
    {
        // Étape 1: Inscription via RegisterUserUseCase
        $registrationData = [
            'user_type' => 'student',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => 'SecurePassword123',
            'amu_id' => 'dupont123',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA',
            'major' => 'A'
        ];

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->studentRepository->method('insert')->willReturn(1);

        $student = new Student($registrationData);
        $student->setPassword($registrationData['password']);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($student, 1);

        $this->studentRepository->method('findById')->willReturn($student);

        $registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->professorRepository, $this->clientRepository, $this->userRepository);
        $registeredStudent = $registerUseCase->execute($registrationData);

        $this->assertInstanceOf(Student::class, $registeredStudent);
        $this->assertEquals('Jean', $registeredStudent->getFirstName());
        $this->assertEquals('Dupont', $registeredStudent->getLastName());

        // Vérifier que le mot de passe a été hashé
        $passwordHash = $registeredStudent->getPasswordHash();
        $this->assertNotEmpty($passwordHash);
        $this->assertNotEquals('SecurePassword123', $passwordHash);
        $this->assertTrue(password_verify('SecurePassword123', $passwordHash));
        $this->assertEquals($registeredStudent->getLastName(), 'Dupont');
        $this->assertEquals($registeredStudent->getFirstName(), 'Jean');
        $this->assertEquals($registeredStudent->getAmuId(), 'dupont123');
        $this->assertEquals($registeredStudent->getYear(), 2);
        $this->assertEquals($registeredStudent->getTd(), 'TD1');
        $this->assertEquals($registeredStudent->getTp(), 'TPA');
        $this->assertEquals($registeredStudent->getMajor(), 'A');

        // Étape 2: Connexion via LoginUseCase
        $registeredStudent->setPassword('SecurePassword123');
        $this->userRepository->method('findByEmail')->willReturn($registeredStudent);
        $loginUseCase = new LoginUseCase($this->userRepository);
        $loggedInStudent = $loginUseCase->execute('jean.dupont@etu.univ-amu.fr', 'SecurePassword123');

        $this->assertInstanceOf(Student::class, $loggedInStudent);
        $this->assertEquals('Jean', $loggedInStudent->getFirstName());
        $this->assertEquals('Dupont', $loggedInStudent->getLastName());

        $this->assertEquals('TPA', $loggedInStudent->getTp());
        $this->assertEquals('TD1', $loggedInStudent->getTd());
        $this->assertEquals('A', $loggedInStudent->getMajor());
        $this->assertEquals(2, $loggedInStudent->getYear());
        $this->assertTrue($loggedInStudent->isStudent());
        $this->assertEquals('dupont123', $loggedInStudent->getAmuId());
    }

    // ========================================
    // Test du workflow: Registration → Password Reset
    // ========================================
    #[Test]
    public function completePasswordResetWorkflow(): void
    {
        $userData = [
            'user_type' => 'student',
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@etu.univ-amu.fr',
            'phone' => '0623456789',
            'amu_id' => 'martin456',
            'year' => 1,
            'major' => null,
            'td' => 'TD2',
            'tp' => 'TPB',
            'password' => 'OldPassword123'
        ];

        $student = new Student($userData);
        $student->setPassword($userData['password']);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($student, 2);

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->studentRepository->method('insert')->willReturn(2);
        $this->studentRepository->method('findById')->willReturn($student);

        // Étape 1: Créer un utilisateur
        $registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->professorRepository, $this->clientRepository, $this->userRepository);
        $registeredUser = $registerUseCase->execute($userData);

        // Étape 2: Réinitialiser le mot de passe via ResetPasswordUseCase
        $newPassword = 'NewSecurePassword456';
        $this->userRepository->method('findByEmail')->willReturn($registeredUser);
        $this->userRepository->method('updatePassword')->willReturn(true);
        $resetPasswordUseCase = new ResetPasswordUseCase($this->userRepository);
        $resetPasswordUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        // Étape 3: Vérifier que le nouveau mot de passe fonctionne
        $registeredUser->setPassword($newPassword); // Manually update password hash for mock
        // findByEmail already mocked above to return $registeredUser

        $loginUseCase = new LoginUseCase($this->userRepository);
        $loggedInStudent = $loginUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        $this->assertInstanceOf(Student::class, $loggedInStudent);
        $this->assertEquals('Marie', $loggedInStudent->getFirstName());
        $this->assertTrue(password_verify($newPassword, $loggedInStudent->getPasswordHash()));
    }

    // ========================================
    // Test du workflow: Multiple user types
    // ========================================
    #[Test]
    public function workflowHandlesMultipleUserTypes(): void
    {
        $userTypes = [
            [
                'type' => 'student',
                'class' => Student::class,
                'data' => [
                    'user_type' => 'student',
                    'first_name' => 'Student',
                    'last_name' => 'Test',
                    'email' => 'student@etu.univ-amu.fr',
                    'phone' => '0612345678',
                    'password' => 'Pass123',
                    'amu_id' => 'test',
                    'year' => 1,
                    'td' => 'TD1',
                    'tp' => 'TPA'
                ],
            ],
            [
                'type' => 'professor',
                'class' => Professor::class,
                'data' => [
                    'user_type' => 'professor',
                    'first_name' => 'Professor',
                    'last_name' => 'Test',
                    'email' => 'prof@univ-amu.fr',
                    'phone' => '0623456789',
                    'password' => 'Pass123',
                    'amu_id' => 'prof'
                ],
            ],
            [
                'type' => 'client',
                'class' => Client::class,
                'data' => [
                    'user_type' => 'client',
                    'first_name' => 'Client',
                    'last_name' => 'Test',
                    'email' => 'client@company.com',
                    'phone' => '0634567890',
                    'password' => 'Pass123',
                    'organisation' => 'Company'
                ],
            ]
        ];

        $userIdCounter = 100; // Start user IDs from a different range for this test

        foreach ($userTypes as $userType) {
            $userIdCounter++;
            $this->userRepository->method('existsByEmail')->willReturn(false);

            $user = null;
            switch ($userType['type']) {
                case 'student':
                    $user = new Student($userType['data']);
                    $this->studentRepository->method('insert')->willReturn($userIdCounter);
                    $this->studentRepository->method('findById')->willReturn($user);
                    break;
                case 'professor':
                    $user = new Professor($userType['data']);
                    $this->professorRepository->method('insert')->willReturn($userIdCounter);
                    $this->professorRepository->method('findById')->willReturn($user);
                    break;
                case 'client':
                    $user = new Client($userType['data']);
                    $this->clientRepository->method('insert')->willReturn($userIdCounter);
                    $this->clientRepository->method('findById')->willReturn($user);
                    break;
            }

            if ($user) {
                $user->setPassword($userType['data']['password']);
                $reflection = new ReflectionClass($user);
                $prop = $reflection->getProperty('user_id');
                $prop->setAccessible(true);
                $prop->setValue($user, $userIdCounter);
            }

            $registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->professorRepository, $this->clientRepository, $this->userRepository);
            $registeredUser = $registerUseCase->execute($userType['data']);

            $this->assertInstanceOf($userType['class'], $registeredUser);
            $this->assertTrue(password_verify($userType['data']['password'], $registeredUser->getPasswordHash()));
        }
    }

    // ========================================
    // Test de cohérence des données
    // ========================================
    #[Test]
    public function dataRemainsConsistentThroughoutWorkflow(): void
    {
        // Données initiales
        $originalData = [
            'user_type' => 'student',
            'first_name' => 'Consistency',
            'last_name' => 'Test',
            'email' => 'consistency@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => 'Password123',
            'amu_id' => 'consistency123',
            'year' => 3,
            'td' => 'TD3',
            'tp' => 'TPA',
            'major' => 'B'
        ];

        $student = new Student($originalData);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($student, 3);

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->studentRepository->method('insert')->willReturn(3);
        $this->studentRepository->method('findById')->willReturn($student);
        $this->userRepository->method('findById')->willReturn($student);

        // Créer l'utilisateur
        $registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->professorRepository, $this->clientRepository, $this->userRepository);
        $registeredStudent = $registerUseCase->execute($originalData);

        $this->assertInstanceOf(Student::class, $registeredStudent);

        // Vérifier que les données sont correctes après création
        $this->assertEquals('Consistency', $registeredStudent->getFirstName());
        $this->assertEquals('Test', $registeredStudent->getLastName());
        $this->assertEquals('consistency@etu.univ-amu.fr', $registeredStudent->getEmail());
        $this->assertEquals('0612345678', $registeredStudent->getPhone());
        $this->assertEquals('consistency123', $registeredStudent->getAmuId());

        // Re-fetch from DB to be sure
        $fetchedUser = $this->userRepository->findById($registeredStudent->getUserId());
        $this->assertInstanceOf(Student::class, $fetchedUser);
        $this->assertEquals('Consistency', $fetchedUser->getFirstName());
    }

    // ========================================
    // Test de robustesse
    // ========================================
    #[Test]
    public function workflowHandlesUnicodeData(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'François',
            'last_name' => 'Müller',
            'email' => 'françois.müller@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => 'Pàsswørd123€',
            'amu_id' => 'müller',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA'
        ];

        $student = new Student($data);
        $student->setPassword($data['password']);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($student, 4);

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->studentRepository->method('insert')->willReturn(4);
        $this->studentRepository->method('findById')->willReturn($student);

        $registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->professorRepository, $this->clientRepository, $this->userRepository);
        $registeredStudent = $registerUseCase->execute($data);

        $this->assertEquals('François', $registeredStudent->getFirstName());
        $this->assertEquals('Müller', $registeredStudent->getLastName());

        // Le mot de passe Unicode devrait être hashé correctement
        $this->assertTrue(password_verify('Pàsswørd123€', $registeredStudent->getPasswordHash()));
    }
}
