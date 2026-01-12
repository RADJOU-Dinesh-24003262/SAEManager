<?php

namespace Tests\Unit\Models\SAE;

use Core\includes\Database;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Models\BaseModel;
use Marios\Pizza\Base;
use Models\SAE\SAE;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Models\SAE\Repository\SAESubjectRepository;
use Models\SAE\Repository\SAEGroupRepository;
use Models\SAE\SAEGroup;
use Models\SAE\SAESubject;
use Models\User\User;

#[CoversClass(SAE::class)]
#[CoversClass(SAESubjectRepository::class)]
#[CoversClass(SAEGroupRepository::class)]
#[CoversClass(Database::class)]
#[CoversClass(Professor::class)]
#[CoversClass(User::class)]
#[CoversClass(BaseModel::class)]
#[CoversClass(SAEGroup::class)]
#[CoversClass(SAESubject::class)]
#[CoversClass(Student::class)]
class SAEFacadeTest extends TestCase
{
    private Database $db;
    private SAE $saeFacade;

    protected function setUp(): void
    {
        putenv('APP_ENV=testing');

        // Reset Database
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->db = Database::getInstance();
        $this->seedDatabase();

        // Reset Repositories
        $reflectionSubjectRepo = new ReflectionClass(SAESubjectRepository::class);
        $instanceSubjectRepo = $reflectionSubjectRepo->getProperty('instance');
        $instanceSubjectRepo->setAccessible(true);
        $instanceSubjectRepo->setValue(null, null);

        $reflectionGroupRepo = new ReflectionClass(SAEGroupRepository::class);
        $instanceGroupRepo = $reflectionGroupRepo->getProperty('instance');
        $instanceGroupRepo->setAccessible(true);
        $instanceGroupRepo->setValue(null, null);

        // Reset SAE Singleton
        $reflectionSae = new ReflectionClass(SAE::class);
        $instanceSae = $reflectionSae->getProperty('instance');
        $instanceSae->setAccessible(true);
        $instanceSae->setValue(null, null);

        $this->saeFacade = SAE::getInstance();
    }

    private function seedDatabase(): void
    {
        $this->db->exec("DELETE FROM participated_in");
        $this->db->exec("DELETE FROM sae_groups");
        $this->db->exec("DELETE FROM sae_subjects");
        $this->db->exec("DELETE FROM students");
        $this->db->exec("DELETE FROM professors");
        $this->db->exec("DELETE FROM clients");
        $this->db->exec("DELETE FROM users");

        // Prof (ID 2)
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (2, 'Prof', 'Test', 'prof@test.fr', '0000000000', 'hash', '1')");
        $this->db->exec("INSERT INTO professors (professor_id, amu_id) VALUES (2, 'prof1')");

        // Student (ID 1)
        $this->db->exec("INSERT INTO users (user_id, first_name, last_name, email, phone, hashed_password, user_type) VALUES (1, 'Student', 'Test', 'student@test.fr', '0000000000', 'hash', '0')");
        $this->db->exec("INSERT INTO students (student_id, amu_id, year, td, tp) VALUES (1, 'student1', 1, 'TD1', 'TP1')");
    }

    #[Test]
    public function createSaeAsProfessorSuccess(): void
    {
        $prof = new Professor();
        $prof->fetchData('prof@test.fr');

        $data = [
            'subject_name' => 'New SAE',
            'begin_date' => '2023-09-01',
            'end_date' => '2024-06-01',
            'responsible_prof_id' => $prof->getUserId()
        ];

        $subject = $this->saeFacade->createSAE($prof, $data);

        $this->assertNotNull($subject);
        $this->assertEquals('New SAE', $subject->getSubjectName());
        $this->assertGreaterThan(0, $subject->getSaeSubjectId());
    }

    #[Test]
    public function createSaeAsStudentThrowsException(): void
    {
        $student = new Student();
        $student->fetchData('student@test.fr');

        $data = [
            'subject_name' => 'Hacked SAE',
            'begin_date' => '2023-09-01',
            'end_date' => '2024-06-01',
            'responsible_prof_id' => 2
        ];

        $this->expectException(ExceptionAccessDenied::class);
        $this->saeFacade->createSAE($student, $data);
    }

    #[Test]
    public function createGroupAsResponsibleProfessorSuccess(): void
    {
        // 1. Create SAE
        $prof = new Professor();
        $prof->fetchData('prof@test.fr');
        $saeData = [
            'subject_name' => 'My SAE',
            'begin_date' => '2023-01-01',
            'end_date' => '2023-06-01',
            'responsible_prof_id' => $prof->getUserId()
        ];
        $sae = $this->saeFacade->createSAE($prof, $saeData);
        $saeId = (int)$sae->getSaeSubjectId();

        // 2. Create Group
        $group = $this->saeFacade->createGroup($prof, $saeId, $prof->getUserId());

        $this->assertNotNull($group);
        $this->assertEquals($saeId, $group->getSaeSubjectId());
    }

    #[Test]
    public function assignStudentToGroupSuccess(): void
    {
        $prof = new Professor();
        $prof->fetchData('prof@test.fr');

        // Create SAE
        $sae = $this->saeFacade->createSAE($prof, [
            'subject_name' => 'SAE 1',
            'begin_date' => '2023-01-01',
            'end_date' => '2023-06-01',
            'responsible_prof_id' => $prof->getUserId()
        ]);
        $saeId = (int)$sae->getSaeSubjectId();

        // Create Group
        $group = $this->saeFacade->createGroup($prof, $saeId, null);
        $groupId = (int)$group->getSaeGroupId();

        // Assign Student (ID 1)
        $result = $this->saeFacade->assignStudentToGroup($prof, 1, $groupId);

        $this->assertTrue($result);
    }
}
