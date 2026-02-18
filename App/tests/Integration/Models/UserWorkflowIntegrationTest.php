<?php

namespace Tests\Integration\Models;

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
use Core\includes\Database;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\RegisterUserUseCase;
use Models\UseCase\User\LoginUseCase;
use Models\UseCase\User\ResetPasswordUseCase;
use ReflectionClass;

/**
 * Tests d'intégration pour les workflows complets User
 */
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
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

class UserWorkflowIntegrationTest extends TestCase
{
    private PdoUserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $db = new Database();
        Database::setInstance($db);
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        // Réinitialise l'instance statique Database
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $emails = [
            'jean.dupont@etu.univ-amu.fr',
            'marie.martin@etu.univ-amu.fr',
            'student@etu.univ-amu.fr',
            'prof@univ-amu.fr',
            'client@company.com',
            'consistency@etu.univ-amu.fr',
            'security@etu.univ-amu.fr',
            'françois.müller@etu.univ-amu.fr'
        ];

        $this->userRepository = new PdoUserRepository();

        foreach ($emails as $email) {
            if ($this->userRepository->existsByEmail($email)) {
                $user = $this->userRepository->findByEmail($email);
                if ($user) {
                    $this->userRepository->delete($user->getUserId());
                }
            }
        }
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

        $studentRepository = new PdoStudentRepository();
        $clientRepository = new PdoClientRepository();
        $professorRepository = new PdoProfessorRepository();
        $userRepository = new PdoUserRepository();


        $registerUseCase = new RegisterUserUseCase($studentRepository, $professorRepository, $clientRepository, $userRepository);
        $student = $registerUseCase->execute($registrationData);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Jean', $student->getFirstName());
        $this->assertEquals('Dupont', $student->getLastName());

        // Vérifier que le mot de passe a été hashé
        $passwordHash = $student->getPasswordHash();
        $this->assertNotEmpty($passwordHash);
        $this->assertNotEquals('SecurePassword123', $passwordHash);
        $this->assertTrue(password_verify('SecurePassword123', $passwordHash));
        $this->assertEquals($student->getLastName(), 'Dupont');
        $this->assertEquals($student->getFirstName(), 'Jean');
        $this->assertEquals($student->getAmuId(), 'dupont123');
        $this->assertEquals($student->getYear(), 2);
        $this->assertEquals($student->getTd(), 'TD1');
        $this->assertEquals($student->getTp(), 'TPA');
        $this->assertEquals($student->getMajor(), 'A');

        // Étape 2: Connexion via LoginUseCase
        $loginUseCase = new LoginUseCase($userRepository);
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

        $studentRepository = new PdoStudentRepository();
        $clientRepository = new PdoClientRepository();
        $professorRepository = new PdoProfessorRepository();
        $userRepository = new PdoUserRepository();

        // Étape 1: Créer un utilisateur
        $registerUseCase = new RegisterUserUseCase($studentRepository, $professorRepository, $clientRepository, $userRepository);
        $student = $registerUseCase->execute([
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
        ]);

        // Étape 2: Réinitialiser le mot de passe via ResetPasswordUseCase
        $newPassword = 'NewSecurePassword456';
        $resetPasswordUseCase = new ResetPasswordUseCase($userRepository);
        $resetPasswordUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        // Étape 3: Vérifier que le nouveau mot de passe fonctionne
        $loginUseCase = new LoginUseCase($userRepository);
        $loggedInStudent = $loginUseCase->execute('marie.martin@etu.univ-amu.fr', $newPassword);

        $this->assertInstanceOf(Student::class, $loggedInStudent);
        $this->assertEquals('Marie', $loggedInStudent->getFirstName());
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
            $studentRepository = new PdoStudentRepository();
            $clientRepository = new PdoClientRepository();
            $professorRepository = new PdoProfessorRepository();
            $userRepository = new PdoUserRepository();


            $registerUseCase = new RegisterUserUseCase($studentRepository, $professorRepository, $clientRepository, $userRepository);
            $user = $registerUseCase->execute($userType['data']);


            $this->assertInstanceOf($userType['class'], $user);
            $this->assertTrue(password_verify($userType['data']['password'], $user->getPasswordHash()));
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

        // Créer l'utilisateur
        $studentRepository = new PdoStudentRepository();
        $clientRepository = new PdoClientRepository();
        $professorRepository = new PdoProfessorRepository();
        $userRepository = new PdoUserRepository();

        $registerUseCase = new RegisterUserUseCase($studentRepository, $professorRepository, $clientRepository, $userRepository);
        $student = $registerUseCase->execute($originalData);

        $this->assertInstanceOf(Student::class, $student);

        // Vérifier que les données sont correctes après création
        $this->assertEquals('Consistency', $student->getFirstName());
        $this->assertEquals('Test', $student->getLastName());
        $this->assertEquals('consistency@etu.univ-amu.fr', $student->getEmail());
        $this->assertEquals('0612345678', $student->getPhone());
        $this->assertEquals('consistency123', $student->getAmuId());

        // Re-fetch from DB to be sure
        $fetchedUser = $this->userRepository->findById($student->getUserId());
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

        $studentrepository = new PdoStudentRepository();
        $clientrepository = new PdoClientRepository();
        $professorrepository = new PdoProfessorRepository();
        $userrepository = new PdoUserRepository();

        $registerUseCase = new RegisterUserUseCase($studentrepository, $professorrepository, $clientrepository, $userrepository);
        $student = $registerUseCase->execute($data);

        $this->assertEquals('François', $student->getFirstName());
        $this->assertEquals('Müller', $student->getLastName());

        // Le mot de passe Unicode devrait être hashé correctement
        $this->assertTrue(password_verify('Pàsswørd123€', $student->getPasswordHash()));
    }
}
