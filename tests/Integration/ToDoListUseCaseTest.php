<?php

namespace App\Tests\Integration;

use Core\includes\Database;
use Models\Entity\ToDoList\ToDoList;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Entity\User\Student;
use Models\Entity\SAE\SAESubject;
use Models\Entity\SAE\SAEGroup;
use Models\UseCase\ToDoList\CreateTaskUseCase;
use Models\UseCase\ToDoList\GetTasksUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use PHPUnit\Framework\TestCase;

class ToDoListUseCaseTest extends TestCase
{
    private PdoToDoListRepository $todoRepository;
    private PdoStudentRepository $studentRepository;
    private PdoSAESubjectRepository $saeSubjectRepository;
    private PdoSAEGroupRepository $saeGroupRepository;
    private PdoParticipatedInRepository $participatedInRepository;

    private int $testStudentId;
    private int $testSaeId;
    private int $testGroupId;

    protected function setUp(): void
    {
        // Reset Database
        $db = Database::getInstance();
        // SQLite compatible clean up
        $db->exec("DELETE FROM sae_todolists");
        $db->exec("DELETE FROM participated_in");
        $db->exec("DELETE FROM students");
        $db->exec("DELETE FROM sae_groups");
        $db->exec("DELETE FROM sae_subjects");
        $db->exec("DELETE FROM professors");
        $db->exec("DELETE FROM clients");
        $db->exec("DELETE FROM users");
        // Reset auto-increment (optional but good for consistent IDs)
        $db->exec("DELETE FROM sqlite_sequence WHERE name IN ('users', 'sae_subjects', 'sae_groups', 'sae_todolists')");

        $this->todoRepository = new PdoToDoListRepository();
        $this->studentRepository = new PdoStudentRepository();
        $this->saeSubjectRepository = new PdoSAESubjectRepository();
        $this->saeGroupRepository = new PdoSAEGroupRepository();
        $this->participatedInRepository = new PdoParticipatedInRepository();

        // 1. Create Student
        $student = new Student([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@etu.univ-amu.fr',
            'password' => 'password123',
            'phone' => '0123456789',
            'user_type' => 'student',
            'amu_id' => '12345678',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1'
        ]);
        $student->setPassword('password123');
        $createdStudent = $this->studentRepository->insert($student);
        $this->testStudentId = $createdStudent;

        // 2. Create SAE Subject
        // Create dummy professor first
        $db->exec("INSERT INTO users (last_name, first_name, email, hashed_password, phone, user_type) VALUES ('Prof', 'Test', 'prof@test.com', 'pass', '0000000000', '1')");
        $profId = $db->query("SELECT user_id FROM users WHERE email='prof@test.com'")->fetchColumn();
        $db->exec("INSERT INTO professors (professor_id, amu_id) VALUES ($profId, 'AMUPROF')");

        // Insert SAE with correct columns
        $db->exec("INSERT INTO sae_subjects (subject_name, begin_date, end_date, responsible_prof_id, client_id) VALUES ('SAE Test', '2023-09-01', '2024-06-01', $profId, NULL)");
        $this->testSaeId = $db->query("SELECT sae_subject_id FROM sae_subjects WHERE subject_name='SAE Test'")->fetchColumn();

        // 3. Create SAE Group
        // Insert Group
        $db->exec("INSERT INTO sae_groups (sae_subject_id, professor_id) VALUES ({$this->testSaeId}, $profId)");
        $this->testGroupId = $db->query("SELECT sae_group_id FROM sae_groups WHERE sae_subject_id={$this->testSaeId}")->fetchColumn();

        // 4. Assign Student to Group
        $this->participatedInRepository->assignStudentToGroup($this->testStudentId, $this->testGroupId);
    }

    public function testCreateTask()
    {
        $useCase = new CreateTaskUseCase($this->todoRepository);
        $task = $useCase->execute($this->testGroupId, "Faire le diagramme de classes", 1);

        $this->assertNotNull($task->getTodoId());
        $this->assertEquals("Faire le diagramme de classes", $task->getTodoDesc());
        $this->assertEquals(1, $task->getPriority());
        $this->assertEquals($this->testGroupId, $task->getSaeGroupId());
        $this->assertFalse($task->isChecked());

        // Verify in DB
        $savedTask = $this->todoRepository->findById($task->getTodoId());
        $this->assertNotNull($savedTask);
        $this->assertEquals("Faire le diagramme de classes", $savedTask->getTodoDesc());
    }

    public function testGetTasks()
    {
        // Create some tasks
        $createUseCase = new CreateTaskUseCase($this->todoRepository);
        $createUseCase->execute($this->testGroupId, "Tâche 1", 2);
        $createUseCase->execute($this->testGroupId, "Tâche 2", 1);

        $getUseCase = new GetTasksUseCase($this->todoRepository);
        $tasks = $getUseCase->execute($this->testGroupId);

        $this->assertCount(2, $tasks);
        // Repository orders by priority ASC (1, then 2)
        $this->assertEquals("Tâche 2", $tasks[0]->getTodoDesc());
        $this->assertEquals("Tâche 1", $tasks[1]->getTodoDesc());
    }

    public function testUpdateTask()
    {
        $createUseCase = new CreateTaskUseCase($this->todoRepository);
        $task = $createUseCase->execute($this->testGroupId, "Tâche à modifier", 2);

        $updateUseCase = new UpdateTaskUseCase($this->todoRepository);
        $updateUseCase->execute($task->getTodoId(), [
            'checked' => true,
            'priority' => 3
        ]);

        $updatedTask = $this->todoRepository->findById($task->getTodoId());
        $this->assertTrue($updatedTask->isChecked());
        $this->assertEquals(3, $updatedTask->getPriority());
    }

    public function testDeleteTask()
    {
        $createUseCase = new CreateTaskUseCase($this->todoRepository);
        $task = $createUseCase->execute($this->testGroupId, "Tâche à supprimer", 2);

        $deleteUseCase = new DeleteTaskUseCase($this->todoRepository);
        $deleteUseCase->execute($task->getTodoId());

        $deletedTask = $this->todoRepository->findById($task->getTodoId());
        $this->assertNull($deletedTask);
    }
}