<?php

namespace Tests\Unit\Models\User;

use Core\includes\Database;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(Database::class)]
class UserAuthorizationTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        // Force testing environment to use SQLite in-memory DB
        putenv('APP_ENV=testing');

        // Reset the singleton to ensure a fresh connection/database for each test
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->db = Database::getInstance();
        $this->seedDatabase();
    }

    private function seedDatabase(): void
    {
        // Clear existing data (from Database class initialization)
        $this->db->exec("DELETE FROM participated_in");
        $this->db->exec("DELETE FROM sae_groups");
        $this->db->exec("DELETE FROM sae_subjects");
        $this->db->exec("DELETE FROM students");
        $this->db->exec("DELETE FROM professors");
        $this->db->exec("DELETE FROM clients");
        $this->db->exec("DELETE FROM users");

        // 1. Create Users
        // ID 1: Student
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (1, 'Student', 'One', 'student@test.fr', '0000000000', 'hash', '0')");
        $this->db->exec("INSERT INTO students (student_id, amu_id, year, td, tp) VALUES (1, 'student1', 1, 'TD1', 'TP1')");

        // ID 2: Responsible Professor
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (2, 'Resp', 'Prof', 'resp@test.fr', '0000000000', 'hash', '1')");
        $this->db->exec("INSERT INTO professors (professor_id, amu_id) VALUES (2, 'resp1')");

        // ID 3: Other Professor (Assigned to group)
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (3, 'Other', 'Prof', 'other@test.fr', '0000000000', 'hash', '1')");
        $this->db->exec("INSERT INTO professors (professor_id, amu_id) VALUES (3, 'other1')");

        // ID 4: Client
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (4, 'Client', 'One', 'client@test.fr', '0000000000', 'hash', '2')");
        $this->db->exec("INSERT INTO clients (client_id, organisation) VALUES (4, 'Org1')");

        // ID 5: Unrelated Student
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (5, 'Student', 'Two', 'student2@test.fr', '0000000000', 'hash', '0')");
        $this->db->exec("INSERT INTO students (student_id, amu_id, year, td, tp) VALUES (5, 'student2', 1, 'TD1', 'TP1')");


        // 2. Create SAE Subject (ID 1)
        // Responsible: ID 2, Client: ID 4
        $this->db->exec("INSERT INTO sae_subjects (sae_subject_id, responsible_prof_id, client_id, subject_name, begin_date, end_date) 
                         VALUES (1, 2, 4, 'Test SAE', '2023-01-01', '2023-06-01')");

        // 3. Create SAE Group (ID 1) linked to SAE 1
        // Assigned Prof: ID 3
        $this->db->exec("INSERT INTO sae_groups (sae_group_id, sae_subject_id, professor_id) VALUES (1, 1, 3)");

        // 4. Assign Student 1 to Group 1
        $this->db->exec("INSERT INTO participated_in (student_id, sae_group_id, sae_subject_id) VALUES (1, 1, 1)");
    }

    // ==========================================
    // Student Tests
    // ==========================================

    #[Test]
    public function studentCanAccessSaeIfParticipating(): void
    {
        $student = new Student();
        $student->fetchData('student@test.fr'); // Loads ID 1

        $this->assertTrue($student->canAccessSAE(1), 'Student should access SAE they participate in');
    }

    #[Test]
    public function studentCannotAccessSaeIfNotParticipating(): void
    {
        $student = new Student();
        $student->fetchData('student2@test.fr'); // Loads ID 5 (Unrelated)

        $this->assertFalse($student->canAccessSAE(1), 'Student should NOT access SAE they are not part of');
    }

    #[Test]
    public function studentCannotManageSae(): void
    {
        $student = new Student();
        $student->fetchData('student@test.fr');

        $this->assertFalse($student->canManageSAE(1), 'Student cannot manage SAE');
        $this->assertFalse($student->canManageSAE(null), 'Student cannot create SAE');
    }

    // ==========================================
    // Professor Tests
    // ==========================================

    #[Test]
    public function responsibleProfessorCanAccessSae(): void
    {
        $prof = new Professor();
        $prof->fetchData('resp@test.fr'); // ID 2

        $this->assertTrue($prof->canAccessSAE(1), 'Responsible professor should access SAE');
    }

    #[Test]
    public function responsibleProfessorCanManageSae(): void
    {
        $prof = new Professor();
        $prof->fetchData('resp@test.fr'); // ID 2

        $this->assertTrue($prof->canManageSAE(1), 'Responsible professor should manage SAE');
    }

    #[Test]
    public function assignedProfessorCanAccessSae(): void
    {
        $prof = new Professor();
        $prof->fetchData('other@test.fr'); // ID 3 (Assigned to Group 1)

        $this->assertTrue($prof->canAccessSAE(1), 'Assigned professor should access SAE');
    }

    #[Test]
    public function assignedProfessorCannotManageSae(): void
    {
        $prof = new Professor();
        $prof->fetchData('other@test.fr'); // ID 3

        $this->assertFalse($prof->canManageSAE(1), 'Assigned professor (not responsible) cannot manage SAE');
    }

    #[Test]
    public function anyProfessorCanCreateSae(): void
    {
        $prof = new Professor();
        $prof->fetchData('other@test.fr');

        $this->assertTrue($prof->canManageSAE(null), 'Any professor should be able to create (manage null) SAE');
    }

    // ==========================================
    // Client Tests
    // ==========================================

    #[Test]
    public function clientCanAccessTheirSae(): void
    {
        $client = new Client();
        $client->fetchData('client@test.fr'); // ID 4 (Client of SAE 1)

        $this->assertTrue($client->canAccessSAE(1), 'Client should access their SAE');
    }

    #[Test]
    public function clientCannotAccessOtherSae(): void
    {
        // Create another SAE (ID 2) where client is NOT the client
        $this->db->exec("INSERT INTO sae_subjects (sae_subject_id, responsible_prof_id, client_id, subject_name, begin_date, end_date) 
                         VALUES (2, 2, NULL, 'Other SAE', '2023-01-01', '2023-06-01')");

        $client = new Client();
        $client->fetchData('client@test.fr');

        $this->assertFalse($client->canAccessSAE(2), 'Client should NOT access SAE they are not linked to');
    }

    #[Test]
    public function clientCannotManageSae(): void
    {
        $client = new Client();
        $client->fetchData('client@test.fr');

        $this->assertFalse($client->canManageSAE(1), 'Client cannot manage SAE');
        $this->assertFalse($client->canManageSAE(null), 'Client cannot create SAE');
    }
}
