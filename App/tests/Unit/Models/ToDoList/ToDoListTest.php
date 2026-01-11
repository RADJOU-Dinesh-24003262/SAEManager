<?php

namespace Tests\Unit\Models\ToDoList;

use App\Models\ToDoList\ToDoList;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ToDoList::class)]
class ToDoListTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $data = [
            'todo_id' => 1,
            'sae_subject_id' => 10,
            'groupId' => 5,
            'tododesc' => 'Test task'
        ];

        $todo = new ToDoList($data);

        $this->assertEquals(1, $todo->getTodoId());
        $this->assertEquals(10, $todo->getSaeSubjectId());
        $this->assertEquals(5, $todo->getSaeGroupId());
        $this->assertEquals('Test task', $todo->getTodoDesc());
    }

    #[Test]
    public function settersWorkCorrectly(): void
    {
        $todo = new ToDoList();
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
        $todo = new ToDoList();
        $this->assertEquals(2, $todo->getPriority()); // Default should be 2 (Medium)
    }
}
