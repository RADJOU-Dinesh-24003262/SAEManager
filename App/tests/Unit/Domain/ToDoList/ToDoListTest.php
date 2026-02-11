<?php

namespace Tests\Unit\Domain\ToDoList;

use App\Domain\ToDoList\ToDoList;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ToDoList::class)]
class ToDoListTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $groupId = 1;
        $description = 'Test task';
        $priority = 1;
        $checked = true;
        $todoId = 10;

        $todo = new ToDoList($groupId, $description, $priority, $checked, $todoId);

        $this->assertEquals($todoId, $todo->getTodoId());
        $this->assertEquals($groupId, $todo->getGroupId());
        $this->assertEquals($description, $todo->getDescription());
        $this->assertEquals($priority, $todo->getPriority());
        $this->assertTrue($todo->isChecked());
    }

    #[Test]
    public function settersWorkCorrectly(): void
    {
        $todo = new ToDoList(1, 'Initial');

        $todo->setTodoId(1);
        $todo->setGroupId(5);
        $todo->setDescription('New task');
        $todo->setChecked(true);
        $todo->setPriority(1);

        $this->assertEquals(1, $todo->getTodoId());
        $this->assertEquals(5, $todo->getGroupId());
        $this->assertEquals('New task', $todo->getDescription());
        $this->assertTrue($todo->isChecked());
        $this->assertEquals(1, $todo->getPriority());
    }

    #[Test]
    public function defaultPriorityIsMedium(): void
    {
        $todo = new ToDoList(1, 'Desc');
        $this->assertEquals(2, $todo->getPriority());
    }
}