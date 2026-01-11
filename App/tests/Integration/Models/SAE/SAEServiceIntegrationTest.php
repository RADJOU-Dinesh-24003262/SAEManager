<?php

namespace Tests\Integration\Models\SAE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\SAE\SAE;
use Models\User\User;
use Models\User\Professor;
use Models\User\Student;
use Models\User\Client;
use Core\includes\Database;
use ReflectionClass;

#[CoversClass(SAE::class)]
class SAEServiceIntegrationTest extends TestCase
{
    private ?Professor $prof;
    private ?Student $student;
    private ?Client $client;
    private ?int $saeId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we have a fresh database connection
        $db = new Database();
        Database::setInstance($db);

        // Create a Professor
        $this->prof = new Professor([
            'first_name' => 'TestProf',
            'last_name' => 'Integration',
            'email' => 'test.prof.integration@univ-amu.fr',
            'phone' => '0600000001',
            'amu_id' => 'p_int_1'
        ]);
        $this->prof->setPassword('password');
        $this->prof->save();
        $this->prof->fetchData($this->prof->getEmail());

        // Create a Student
        $this->student = new Student([
            'first_name' => 'TestStudent',
            'last_name' => 'Integration',
            'email' => 'test.student.integration@etu.univ-amu.fr',
            'phone' => '0600000002',
            'amu_id' => 's_int_1',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ]);
        $this->student->setPassword('password');
        $this->student->save();
        $this->student->fetchData($this->student->getEmail());

        // Create a Client
        $this->client = new Client([
            'first_name' => 'TestClient',
            'last_name' => 'Integration',
            'email' => 'test.client.integration@company.com',
            'phone' => '0600000003',
            'organisation' => 'Test Corp'
        ]);
        $this->client->setPassword('password');
        $this->client->save();
        $this->client->fetchData($this->client->getEmail());
    }

    protected function tearDown(): void
    {
        // Cleanup
        if ($this->saeId) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("DELETE FROM sae_subjects WHERE sae_subject_id = :id");
                $stmt->execute(['id' => $this->saeId]);
            } catch (\Exception $e) {
            }
        }

        if ($this->prof) {
            try {
                User::deleteByEmail($this->prof->getEmail());
            } catch (\Exception $e) {
            }
        }
        if ($this->student) {
            try {
                User::deleteByEmail($this->student->getEmail());
            } catch (\Exception $e) {
            }
        }
        if ($this->client) {
            try {
                User::deleteByEmail($this->client->getEmail());
            } catch (\Exception $e) {
            }
        }

        // Reset Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    #[Test]
    public function canCreateSAEAndGroups(): void
    {
        $saeService = SAE::getInstance();

        // 1. Create SAE
        $saeData = [
            'subject_name' => 'Integration Test SAE',
            'responsible_prof_id' => $this->prof->getUserId(),
            'client_id' => $this->client->getUserId(),
            'begin_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month')),
            'file_path' => null
        ];

        $subject = $saeService->createSAE($this->prof, $saeData);
        $this->assertNotNull($subject->getSaeSubjectId());
        $this->saeId = $subject->getSaeSubjectId();

        $this->assertEquals($saeData['subject_name'], $subject->getSubjectName());

        // 2. Create Group with Professor
        $group1 = $saeService->createGroup($this->prof, $this->saeId, $this->prof->getUserId());
        $this->assertNotNull($group1->getSaeGroupId());
        $this->assertEquals($this->prof->getUserId(), $group1->getProfessorId());

        // 3. Create Group without Professor (Nullable check)
        $group2 = $saeService->createGroup($this->prof, $this->saeId, null);
        $this->assertNotNull($group2->getSaeGroupId());
        $this->assertNull($group2->getProfessorId());

        // 4. Assign Student to Group 1
        $assigned = $saeService->assignStudentToGroup($this->prof, $this->student->getUserId(), $group1->getSaeGroupId());
        $this->assertTrue($assigned);

        // 5. Verify Complete Data
        $data = $saeService->getCompleteSAEData($this->saeId, $this->prof);
        $this->assertNotNull($data);
        $this->assertEquals($this->saeId, $data['subject']->getSaeSubjectId());

        // Check groups count (should be at least 2)
        $this->assertGreaterThanOrEqual(2, count($data['groups']));

        // 6. Assign Professor to Group 2
        $updated = $saeService->assignProfessorToGroup($this->prof, $group2->getSaeGroupId(), $this->prof->getUserId());
        $this->assertTrue($updated);

        // 7. Remove Student
        $removed = $saeService->removeStudentFromGroup($this->prof, $this->student->getUserId(), $group1->getSaeGroupId());
        $this->assertTrue($removed);

        // 8. Delete Group
        $deletedGroup = $saeService->deleteGroup($this->prof, $group1->getSaeGroupId());
        $this->assertTrue($deletedGroup);
    }
}
