<?php

namespace Integration\UseCase\ToDoList;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Models\Entity\ToDoItem\ToDoItem;
use Models\UseCase\ToDoList\CreateTaskUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;

#[CoversClass(TodoItem::class)]
#[CoversClass(CreateTaskUseCase::class)]
#[CoversClass(DeleteTaskUseCase::class)]
#[CoversClass(UpdateTaskUseCase::class)]
class ToDoListIntegrationTest extends TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ToDoListInterface::class);
    }

    public function testCreateTask()
    {
        $this->repository
            ->method('insert')
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
    }

    public function testUpdateTask()
    {
        $task = new ToDoItem([
            'todoid' => 1,
            'sae_group_id' => 17,
            'tododesc' => "Tâche à modifier",
            'priority' => 2,
            'checked' => false,
            'end_date' => "2026-03-29"
        ]);

        $this->repository
            ->method('findById')
            ->willReturn($task);

        $this->repository
            ->method('update')
            ->willReturn(true);

        $useCase = new UpdateTaskUseCase($this->repository);

        $useCase->execute(1, [
            'checked' => true,
            'priority' => 3,
            'end_date' => "2026-03-31"
        ]);

        $this->assertTrue($task->isChecked());
        $this->assertEquals(3, $task->getPriority());
    }

    public function testDeleteTask()
    {
        $this->repository
            ->method('delete')
            ->willReturn(true);

        $useCase = new DeleteTaskUseCase($this->repository);

        $useCase->execute(1);

        $this->assertTrue(true);
    }
}
