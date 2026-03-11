<?php

namespace Integration\UseCase\ToDoList;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Models\Entity\ToDoItem\ToDoItem;
use Models\UseCase\ToDoList\CreateTaskUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use Models\Repository\ToDoList\PdoToDoListRepository;

#[CoversClass(TodoItem::class)]
#[CoversClass(CreateTaskUseCase::class)]
#[CoversClass(DeleteTaskUseCase::class)]
#[CoversClass(UpdateTaskUseCase::class)]
class ToDoListIntegrationTest extends TestCase
{
    private PdoToDoListRepository $repository;
    private $pdoMock;
    private $statementMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PdoToDoListRepository::class);
        $this->statementMock = $this->createMock(PdoToDoListRepository::class);

        $this->repository = new PdoToDoListRepository($this->pdoMock);
    }

    public function testCreateTask()
    {
        $this->pdoMock
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock
            ->method('lastInsertId')
            ->willReturn(1);

        $useCase = new CreateTaskUseCase($this->repository);

        $task = $useCase->execute(
            17,
            "Faire le diagramme de classes",
            1,
            "2026-04-20"
        );

        $this->assertInstanceOf(ToDoItem::class, $task);
        $this->assertEquals(1, $task->getTodoId());
        $this->assertEquals("Faire le diagramme de classes", $task->getTodoDesc());
        $this->assertEquals(1, $task->getPriority());
        $this->assertFalse($task->isChecked());
    }

    public function testUpdateTask()
    {
        $taskData = [
            'todoid' => 1,
            'sae_group_id' => 17,
            'tododesc' => "Tâche à modifier",
            'priority' => 2,
            'checked' => false,
            'end_date' => "2026-03-29"
        ];

        $this->pdoMock
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock
            ->method('execute')
            ->willReturn(true);

        $this->statementMock
            ->method('fetch')
            ->willReturn($taskData);

        $updateUseCase = new UpdateTaskUseCase($this->repository);

        $updateUseCase->execute(1, [
            'checked' => true,
            'priority' => 3,
            'end_date' => "2026-03-31"
        ]);

        $this->assertTrue(true); // si aucune exception → test OK
    }

    public function testDeleteTask()
    {
        $this->pdoMock
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock
            ->method('execute')
            ->willReturn(true);

        $deleteUseCase = new DeleteTaskUseCase($this->repository);

        $deleteUseCase->execute(1);

        $this->assertTrue(true);
    }
}
