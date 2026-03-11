<?php

namespace Integration\UseCase\ToDoList;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Models\Entity\ToDoItem\ToDoItem;
use Models\UseCase\ToDoList\CreateTaskUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Core\Includes\Database;

#[CoversClass(TodoItem::class)]
#[CoversClass(CreateTaskUseCase::class)]
#[CoversClass(DeleteTaskUseCase::class)]
#[CoversClass(UpdateTaskUseCase::class)]
class ToDoListIntegrationTest extends TestCase
{
    private PdoToDoListRepository $repository;

    protected function setUp(): void
    {
        $db = Database::getInstance();
        $this->repository = new PdoToDoListRepository($db);
    }

    public function testCreateTask()
    {
        $useCase = new CreateTaskUseCase($this->repository);

        $task = $useCase->execute(
            17,
            "Faire le diagramme de classes",
            1,
            "2026-04-20"
        );

        $this->assertInstanceOf(ToDoItem::class, $task);
        $this->assertNotNull($task->getTodoId());
        $this->assertEquals("Faire le diagramme de classes", $task->getTodoDesc());
        $this->assertEquals(1, $task->getPriority());
        $this->assertFalse($task->isChecked());

        $savedTask = $this->repository->findById($task->getTodoId());

        $this->assertNotNull($savedTask);
        $this->assertEquals($task->getTodoDesc(), $savedTask->getTodoDesc());
    }

    public function testUpdateTask()
    {
        $createUseCase = new CreateTaskUseCase($this->repository);

        $task = $createUseCase->execute(
            17,
            "Tâche à modifier",
            2,
            "2026-03-29"
        );

        $updateUseCase = new UpdateTaskUseCase($this->repository);

        $updateUseCase->execute($task->getTodoId(), [
            'Checked' => true,
            'Priority' => 3,
            'end_date' => "2026-03-31"
        ]);

        $updatedTask = $this->repository->findById($task->getTodoId());

        $this->assertTrue($updatedTask->isChecked());
        $this->assertEquals(3, $updatedTask->getPriority());
        $this->assertEquals("2026-03-31", $updatedTask->getEndDate());
    }

    public function testDeleteTask()
    {
        $createUseCase = new CreateTaskUseCase($this->repository);

        $task = $createUseCase->execute(
            17,
            "Tâche à supprimer",
            2,
            "2026-03-29"
        );

        $deleteUseCase = new DeleteTaskUseCase($this->repository);

        $deleteUseCase->execute($task->getTodoId());

        $deletedTask = $this->repository->findById($task->getTodoId());

        $this->assertNull($deletedTask);
    }
}
