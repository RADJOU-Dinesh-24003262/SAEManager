<?php

namespace Tests\Integration\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\User\User;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use Core\includes\Database;

/**
 * Tests d'intégration pour les workflows complets User
 */
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(Database::class)]
class UserWorkflowIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $db = new \Core\includes\Database();
        \Core\includes\Database::setInstance($db);
    }

    protected function tearDown(): void
    {
        // Réinitialise l'instance statique Database
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        parent::tearDown();
    }

    // ========================================
    // Test du workflow complet: Registration → Login
    // ========================================

    #[Test]
    public function completeRegistrationAndLoginWorkflowForStudent(): void
    {
        // Étape 1: Création d'un utilisateur via createFromRegistrationData
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

        $student = User::createFromRegistrationData($registrationData);
        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Jean', $student->getFirstName());
        $this->assertEquals('Dupont', $student->getLastName());
        // Vérifier que le mot de passe a été hashé
        $passwordHash = $student->getPasswordHash();
        $this->assertNotEmpty($passwordHash);
        $this->assertNotEquals('SecurePassword123', $passwordHash);
        $this->assertTrue(password_verify('SecurePassword123', $passwordHash));

        // Étape 2: Sauvegarde réelle
        $student->save();

        // Étape 3: Connexion réelle
        $loginData = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'password' => 'SecurePassword123'
        ];
        $loggedInStudent = User::createFromLoginData($loginData);
        /** @var Student $loggedInStudent */
        $this->assertInstanceOf(Student::class, $loggedInStudent);
        $this->assertEquals('Jean', $loggedInStudent->getFirstName());
        $this->assertEquals('Dupont', $loggedInStudent->getLastName());
        $this->assertEquals('dupont123', $loggedInStudent->getAmuId());
    }

    // ========================================
    // Test du workflow: Registration → Password Reset
    // ========================================

    #[Test]
    public function completePasswordResetWorkflow(): void
    {
        // Étape 1: Créer et sauvegarder un utilisateur
        $student = new Student([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@etu.univ-amu.fr',
            'phone' => '0623456789',
            'amu_id' => 'martin456',
            'year' => 1,
            'td' => 'TD2',
            'tp' => 'TPB'
        ]);
        $student->setPassword('OldPassword123');
        $student->save();

        // Étape 2: Réinitialiser le mot de passe
        $newPassword = 'NewSecurePassword456';
        User::updatePasswordByEmail('marie.martin@etu.univ-amu.fr', $newPassword);

        // Étape 3: Vérifier que le nouveau mot de passe fonctionne
        $loginData = [
            'email' => 'marie.martin@etu.univ-amu.fr',
            'password' => 'NewSecurePassword456'
        ];
        $loggedInStudent = User::createFromLoginData($loginData);
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
                ]
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
                ]
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
                ]
            ]
        ];

        foreach ($userTypes as $userType) {
            $user = User::createFromRegistrationData($userType['data']);
            
            $this->assertInstanceOf($userType['class'], $user);
            $this->assertEquals($userType['type'], $user->getUserType());
            $this->assertTrue(password_verify('Pass123', $user->getPasswordHash()));
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
        $student = User::createFromRegistrationData($originalData);
        $this->assertInstanceOf(Student::class, $student);
        /** @var Student $student */

        // Vérifier que les données sont correctes après création
        $this->assertEquals('Consistency', $student->getFirstName());
        $this->assertEquals('Test', $student->getLastName());
        $this->assertEquals('consistency@etu.univ-amu.fr', $student->getEmail());
        $this->assertEquals('0612345678', $student->getPhone());
        $this->assertEquals('consistency123', $student->getAmuId());
        $this->assertEquals(3, $student->getYear());
        $this->assertEquals('TD3', $student->getTd());
        $this->assertEquals('TPA', $student->getTp());
        $this->assertEquals('B', $student->getParcours());

        // Vérifier plusieurs fois (les getters ne devraient pas modifier les données)
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals('Consistency', $student->getFirstName());
            $this->assertEquals('consistency123', $student->getAmuId());
            $this->assertEquals(3, $student->getYear());
        }
    }

    // ========================================
    // Test de sécurité du workflow
    // ========================================

    #[Test]
    public function passwordNeverExposedInPlainText(): void
    {
        $plainPassword = 'VerySecretPassword123';
        
        $registrationData = [
            'user_type' => 'student',
            'first_name' => 'Security',
            'last_name' => 'Test',
            'email' => 'security@etu.univ-amu.fr',
            'phone' => '0612345678',
            'password' => $plainPassword,
            'amu_id' => 'security',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ];

        $student = User::createFromRegistrationData($registrationData);

        // Le mot de passe ne devrait jamais être stocké en clair
        $hash = $student->getPasswordHash();
        $this->assertNotEquals($plainPassword, $hash);
        
        // Sérialiser l'objet
        $serialized = serialize($student);
        
        // Le mot de passe en clair ne devrait pas apparaître dans la sérialisation
        $this->assertStringNotContainsString($plainPassword, $serialized);
        
        // Mais le hash devrait pouvoir vérifier le mot de passe
        $this->assertTrue(password_verify($plainPassword, $hash));
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

        $student = User::createFromRegistrationData($data);

        $this->assertEquals('François', $student->getFirstName());
        $this->assertEquals('Müller', $student->getLastName());
        $this->assertStringContainsString('ç', $student->getFirstName());
        $this->assertStringContainsString('ü', $student->getLastName());
        
        // Le mot de passe Unicode devrait être hashé correctement
        $this->assertTrue(password_verify('Pàsswørd123€', $student->getPasswordHash()));
    }
}