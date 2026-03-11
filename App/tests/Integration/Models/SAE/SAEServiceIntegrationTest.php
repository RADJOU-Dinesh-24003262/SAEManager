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
use Core\Includes\Database;
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
use Services\FileService;

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
#[CoversClass(FileService::class)]

class SAEServiceIntegrationTest extends TestCase
{
    private $prof;
    private $student;
    private $client;
    private $saeSubjectRepo;
    private $saeGroupRepo;
    private $participatedInRepo;
    private $studentRepo;
    private $professorRepo;
    private $clientRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prof = $this->createMock(Professor::class);
        $this->prof->method('getUserId')->willReturn(1);
        $this->prof->method('isProfessor')->willReturn(true);
        $this->prof->method('getUserType')->willReturn('professor');

        $this->student = $this->createMock(Student::class);
        $this->student->method('getUserId')->willReturn(2);
        $this->student->method('getUserType')->willReturn('student');

        $this->client = $this->createMock(Client::class);
        $this->client->method('getUserId')->willReturn(3);
        $this->client->method('getUserType')->willReturn('client');

        $this->saeSubjectRepo = $this->createMock(PdoSAESubjectRepository::class);
        $this->saeGroupRepo = $this->createMock(PdoSAEGroupRepository::class);
        $this->participatedInRepo = $this->createMock(PdoParticipatedInRepository::class);
        $this->studentRepo = $this->createMock(PdoStudentRepository::class);
        $this->professorRepo = $this->createMock(PdoProfessorRepository::class);
        $this->clientRepo = $this->createMock(PdoClientRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function canCreateSAEAndGroups(): void
    {
        // 1. Create SAE
        $createSaeUseCase = new CreateSAEUseCase($this->saeSubjectRepo);
        $saeData = [
            'subject_name' => 'Integration Test SAE',
            'responsible_prof_id' => 1,
            'client_id' => 3,
            'begin_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month')),
            'file_path' => null
        ];

        $mockSubject = new SAESubject($saeData);
        $reflection = new ReflectionClass(SAESubject::class);
        $prop = $reflection->getProperty('sae_subject_id');
        $prop->setAccessible(true);
        $prop->setValue($mockSubject, 100);

        $this->saeSubjectRepo->method('insert')->willReturn(100);
        $this->saeSubjectRepo->method('findById')->willReturn($mockSubject);

        $subject = $createSaeUseCase->execute($this->prof, $saeData);
        $this->assertEquals(100, $subject->getSaeSubjectId());

        // 2. Create Group with Professor
        $createGroupUseCase = new CreateSAEGroupUseCase($this->saeGroupRepo, $this->saeSubjectRepo);
        $mockGroup1 = new SAEGroup(['sae_subject_id' => 100, 'professor_id' => 1]);
        $reflectionGroup = new ReflectionClass(SAEGroup::class);
        $propGroup = $reflectionGroup->getProperty('sae_group_id');
        $propGroup->setAccessible(true);
        $propGroup->setValue($mockGroup1, 200);

        $this->saeGroupRepo->method('insert')->willReturn(200);
        $this->saeGroupRepo->method('findById')->willReturn($mockGroup1);

        $group1 = $createGroupUseCase->execute($this->prof, 100, 1);
        $this->assertEquals(200, $group1->getSaeGroupId());

        // 4. Assign Student to Group 1
        $assignStudentUseCase = new AssignStudentToGroupUseCase($this->saeGroupRepo, $this->participatedInRepo, $this->saeSubjectRepo);
        $this->participatedInRepo->method('getStudentGroupId')->willReturn(null);
        $this->participatedInRepo->method('assignStudentToGroup')->willReturn(true);
        $assigned = $assignStudentUseCase->execute($this->prof, 2, 200);
        $this->assertTrue($assigned);

        // 5. Verify Complete Data
        $getCompleteDataUseCase = new GetCompleteSAEDataUseCase(
            $this->saeSubjectRepo,
            $this->saeGroupRepo,
            $this->participatedInRepo,
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo
        );

        $this->professorRepo->method('canAccessSAE')->willReturn(true);
        $this->saeGroupRepo->method('findBySaeSubjectId')->willReturn([$mockGroup1]);
        $this->saeGroupRepo->method('getStudentsInGroup')->willReturn([]);
        $this->saeSubjectRepo->method('getResponsibleProfessor')->willReturn(['user_id' => '1', 'first_name' => 'Prof', 'last_name' => 'Test', 'email' => 'prof@test.com', 'amu_id' => 'prof']);
        $this->saeSubjectRepo->method('getAllProfessorsInfo')->willReturn([]);
        $this->saeSubjectRepo->method('getClientInfo')->willReturn(['user_id' => '3', 'first_name' => 'Client', 'last_name' => 'Test', 'email' => 'client@test.com', 'organisation' => 'Org']);

        $data = $getCompleteDataUseCase->execute(100, $this->prof);

        $this->assertNotNull($data);
        $this->assertEquals(100, $data['subject']->getSaeSubjectId());
    }
}
