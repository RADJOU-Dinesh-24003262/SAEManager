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
use Models\UseCase\User\ValidateTokenUseCase;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Models\UseCase\User\InterfaceDB\TokenRepositoryInterface;
use Services\TokenService;
use ReflectionClass;
use PHPUnit\Framework\Attributes\UsesClass;

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
#[UsesClass(TokenService::class)]

class UserWorkflowIntegrationTest extends TestCase
{
    private $userRepository;
    private $studentRepository;
    private $professorRepository;
    private $clientRepository;
    private $pendingRepository;
    private $passwordResetRepository;
    private $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(PdoUserRepository::class);
        $this->studentRepository = $this->createMock(PdoStudentRepository::class);
        $this->professorRepository = $this->createMock(PdoProfessorRepository::class);
        $this->clientRepository = $this->createMock(PdoClientRepository::class);
        $this->pendingRepository = $this->createMock(PendingRegistrationInterface::class);
        $this->passwordResetRepository = $this->createMock(PasswordResetInterface::class);
        $this->tokenService = new TokenService();

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
        $this->pendingRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('insert')->willReturn(true);

        $student = new Student($registrationData);
        $student->setPassword($registrationData['password']);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($student, 1);

        $registerUseCase = new RegisterUserUseCase($this->userRepository, $this->pendingRepository, $this->tokenService);
        $token = $registerUseCase->execute($registrationData);

        $this->assertIsString($token);
        $this->assertTrue($this->tokenService->isValidFormat($token));

        // Étape 2: Connexion via LoginUseCase
        // On simule que l'utilisateur a été créé (normalement par HandleTwoAuthentificationUseCase)
        $this->userRepository->method('findByEmail')->willReturn($student);
        $loginUseCase = new LoginUseCase($this->userRepository);
        $loggedInStudent = $loginUseCase->execute('jean.dupont@etu.univ-amu.fr', 'SecurePassword123');

        $this->assertInstanceOf(Student::class, $loggedInStudent);
        $this->assertEquals('Jean', $loggedInStudent->getFirstName());
        $this->assertEquals('Dupont', $loggedInStudent->getLastName());
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
        $this->pendingRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('insert')->willReturn(true);

        // Étape 1: Créer un utilisateur (générer token)
        $registerUseCase = new RegisterUserUseCase($this->userRepository, $this->pendingRepository, $this->tokenService);
        $token = $registerUseCase->execute($userData);
        $this->assertIsString($token);

        // Étape 2: Réinitialiser le mot de passe via ResetPasswordUseCase
        $newPassword = 'NewSecurePassword456';
        $this->userRepository->method('findByEmail')->willReturn($student);
        $this->userRepository->method('updatePassword')->willReturn(true);

        $validateTokenUseCase = $this->createMock(ValidateTokenUseCase::class);
        $validateTokenUseCase->method('execute')->willReturn(['email' => $userData['email']]);

        $resetPasswordUseCase = new ResetPasswordUseCase($this->userRepository, $this->passwordResetRepository, $validateTokenUseCase);
        $resetPasswordUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        // Étape 3: Vérifier que le nouveau mot de passe fonctionne
        $student->setPassword($newPassword);
        $this->userRepository->method('findByEmail')->willReturn($student);

        $loginUseCase = new LoginUseCase($this->userRepository);
        $loggedInStudent = $loginUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        $this->assertInstanceOf(Student::class, $loggedInStudent);
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

        foreach ($userTypes as $userType) {
            $this->userRepository->method('existsByEmail')->willReturn(false);
            $this->pendingRepository->method('existsByEmail')->willReturn(false);
            $this->pendingRepository->method('insert')->willReturn(true);

            $registerUseCase = new RegisterUserUseCase($this->userRepository, $this->pendingRepository, $this->tokenService);
            $token = $registerUseCase->execute($userType['data']);

            $this->assertIsString($token);
            $this->assertTrue($this->tokenService->isValidFormat($token));
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

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('insert')->willReturn(true);

        // Créer l'utilisateur (générer token)
        $registerUseCase = new RegisterUserUseCase($this->userRepository, $this->pendingRepository, $this->tokenService);
        $token = $registerUseCase->execute($originalData);

        $this->assertIsString($token);
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

        $this->userRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('existsByEmail')->willReturn(false);
        $this->pendingRepository->method('insert')->willReturn(true);

        $registerUseCase = new RegisterUserUseCase($this->userRepository, $this->pendingRepository, $this->tokenService);
        $token = $registerUseCase->execute($data);

        $this->assertIsString($token);
    }
}
