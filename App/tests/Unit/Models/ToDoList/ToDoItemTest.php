<?php

namespace Tests\Unit\Models\ToDoList;

use Models\Entity\ToDoItem\ToDoItem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ToDoItem::class)]
class ToDoItemTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $data = [
            'todoid' => 1,
            'sae_subject_id' => 10,
            'groupId' => 5,
            'tododesc' => 'Test task'
        ];

        $todo = new ToDoItem($data);

        $this->assertEquals(1, $todo->getTodoId());
        $this->assertEquals(10, $todo->getSaeSubjectId());
        $this->assertEquals(5, $todo->getSaeGroupId());
        $this->assertEquals('Test task', $todo->getTodoDesc());
    }

    #[Test]
    public function settersWorkCorrectly(): void
    {
        $todo = new ToDoItem();
        $todo->setTodoId(1);
        $todo->setSaeSubjectId(10);
        $todo->setSaeGroupId(5);
        $todo->setTododesc('New task');
        $todo->setChecked(true);
        $todo->setPriority(1);

        $this->assertEquals(1, $todo->getTodoId());
        $this->assertEquals(10, $todo->getSaeSubjectId());
        $this->assertEquals(5, $todo->getSaeGroupId());
        $this->assertEquals('New task', $todo->getTodoDesc());
        $this->assertTrue($todo->isChecked());
        $this->assertEquals(1, $todo->getPriority());
    }

    #[Test]
    public function defaultPriorityIsMedium(): void
    {
        $todo = new ToDoItem();
        $this->assertEquals(2, $todo->getPriority()); // Default should be 2 (Medium)
    }

    #[Test]
    public function getIdReturnsTodoId(): void
    {
        $todo = new ToDoItem(['todoid' => 123]);
        $this->assertEquals(123, $todo->getId());
    }
}
