<?php

namespace Tests\Integration\Models\SAE;

use Models\Entity\User\UserFactory;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoUserRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\Entity\User\User;
use Models\Entity\User\Professor;
use Models\Entity\User\Student;
use Models\Entity\User\Client;
use Core\includes\Database;
use Core\Models\Repository\BaseRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Entity\SAE\SAEGroup;
use Models\Entity\SAE\SAESubject;
use Models\UseCase\SAE\CreateSAEUseCase;
use Models\UseCase\SAE\CreateSAEGroupUseCase;
use Models\UseCase\SAE\AssignStudentToGroupUseCase;
use Models\UseCase\SAE\RemoveStudentFromGroupUseCase;
use Models\UseCase\SAE\DeleteSAEGroupUseCase;
use Models\UseCase\SAE\AssignProfessorToGroupUseCase;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;
use ReflectionClass;

#[CoversClass(CreateSAEUseCase::class)]
#[CoversClass(CreateSAEGroupUseCase::class)]
#[CoversClass(AssignStudentToGroupUseCase::class)]
#[CoversClass(RemoveStudentFromGroupUseCase::class)]
#[CoversClass(DeleteSAEGroupUseCase::class)]
#[CoversClass(AssignProfessorToGroupUseCase::class)]
#[CoversClass(GetCompleteSAEDataUseCase::class)]
#[CoversClass(BaseRepository::class)]
#[CoversClass(Database::class)]
#[CoversClass(User::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Student::class)]
#[CoversClass(Client::class)]
#[CoversClass(SAEGroup::class)]
#[CoversClass(PdoSAESubjectRepository::class)]
#[CoversClass(SAESubject::class)]
#[CoversClass(PdoSAEGroupRepository::class)]
#[CoversClass(PdoParticipatedInRepository::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(UserFactory::class)]

class SAEServiceIntegrationTest extends TestCase
{
    private ?Professor $prof;
    private ?Student $student;
    private ?Client $client;
    private ?int $saeId = null;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('APP_ENV=testing');

        // Reset Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        // Reset Repositories
        $this->resetSingleton(PdoSAESubjectRepository::class);
        $this->resetSingleton(PdoSAEGroupRepository::class);
        $this->resetSingleton(PdoParticipatedInRepository::class);

        // Ensure we have a fresh database connection
        $db = Database::getInstance();

        // Create a Professor
        $prof = new Professor([
            'first_name' => 'TestProf',
            'last_name' => 'Integration',
            'email' => 'test.prof.integration@univ-amu.fr',
            'phone' => '0600000001',
            'amu_id' => 'p_int_1'
        ]);
        $prof->setPassword('password');
        $userRepository = new PdoProfessorRepository();
        $createdUserId = $userRepository->insert($prof);
        $this->prof = $userRepository->findById($createdUserId);

        // Create a Student
        $student = new Student([
            'first_name' => 'TestStudent',
            'last_name' => 'Integration',
            'email' => 'test.student.integration@etu.univ-amu.fr',
            'phone' => '0600000002',
            'amu_id' => 's_int_1',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ]);
        $student->setPassword('password');
        $userRepository = new PdoStudentRepository();
        $createdUserId = $userRepository->insert($student);
        $this->student = $userRepository->findById($createdUserId);

        // Create a Client
        $client = new Client([
            'first_name' => 'TestClient',
            'last_name' => 'Integration',
            'email' => 'test.client.integration@company.com',
            'phone' => '0600000003',
            'organisation' => 'Test Corp'
        ]);
        $client->setPassword('password');
        $userRepository = new PdoClientRepository();
        $createdUserId = $userRepository->insert($client);
        $this->client = $userRepository->findById($createdUserId);
    }

    private function resetSingleton(string $className): void
    {
        if (class_exists($className)) {
            $reflection = new ReflectionClass($className);
            if ($reflection->hasProperty('instance')) {
                $instance = $reflection->getProperty('instance');
                $instance->setAccessible(true);
                $instance->setValue(null, null);
            }
        }
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
        // Repositories
        $saeSubjectRepo = new PdoSAESubjectRepository();
        $saeGroupRepo = new PdoSAEGroupRepository();
        $participatedInRepo = new PdoParticipatedInRepository();
        $userRepo = new PdoUserRepository();

        // 1. Create SAE
        $createSaeUseCase = new CreateSAEUseCase($saeSubjectRepo);
        $saeData = [
            'subject_name' => 'Integration Test SAE',
            'responsible_prof_id' => $this->prof->getUserId(),
            'client_id' => $this->client->getUserId(),
            'begin_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month')),
            'file_path' => null
        ];

        $subject = $createSaeUseCase->execute($this->prof, $saeData);
        $this->assertNotNull($subject->getSaeSubjectId());
        $this->saeId = $subject->getSaeSubjectId();

        $this->assertEquals($saeData['subject_name'], $subject->getSubjectName());

        // 2. Create Group with Professor
        $createGroupUseCase = new CreateSAEGroupUseCase($saeGroupRepo, $saeSubjectRepo);
        $group1 = $createGroupUseCase->execute($this->prof, $this->saeId, $this->prof->getUserId());
        $this->assertNotNull($group1->getSaeGroupId());
        $this->assertEquals($this->prof->getUserId(), $group1->getProfessorId());

        // 3. Create Group without Professor (Nullable check)
        $group2 = $createGroupUseCase->execute($this->prof, $this->saeId, null);
        $this->assertNotNull($group2->getSaeGroupId());
        $this->assertNull($group2->getProfessorId());

        // 4. Assign Student to Group 1
        $assignStudentUseCase = new AssignStudentToGroupUseCase($saeGroupRepo, $participatedInRepo, $saeSubjectRepo);
        $assigned = $assignStudentUseCase->execute($this->prof, $this->student->getUserId(), $group1->getSaeGroupId());
        $this->assertTrue($assigned);

        // 5. Verify Complete Data
        $getCompleteDataUseCase = new GetCompleteSAEDataUseCase(
            $saeSubjectRepo,
            $saeGroupRepo,
            $participatedInRepo,
            new PdoStudentRepository(),
            new PdoProfessorRepository(),
            new PdoClientRepository()
        );
        $data = $getCompleteDataUseCase->execute($this->saeId, $this->prof);

        $this->assertNotNull($data);
        $this->assertEquals($this->saeId, $data['subject']->getSaeSubjectId());

        // Check groups count (should be at least 2)
        $this->assertGreaterThanOrEqual(2, count($data['groups']));

        // 6. Assign Professor to Group 2
        $assignProfUseCase = new AssignProfessorToGroupUseCase($saeGroupRepo, $saeSubjectRepo);
        $updated = $assignProfUseCase->execute($this->prof, $group2->getSaeGroupId(), $this->prof->getUserId());
        $this->assertTrue($updated);

        // 7. Remove Student
        $removeStudentUseCase = new RemoveStudentFromGroupUseCase($saeGroupRepo, $participatedInRepo, $saeSubjectRepo);
        $removed = $removeStudentUseCase->execute($this->prof, $this->student->getUserId(), $group1->getSaeGroupId());
        $this->assertTrue($removed);

        // 8. Delete Group
        $deleteGroupUseCase = new DeleteSAEGroupUseCase($saeGroupRepo, $saeSubjectRepo);
        $deletedGroup = $deleteGroupUseCase->execute($this->prof, $group1->getSaeGroupId());
        $this->assertTrue($deletedGroup);
    }
}
