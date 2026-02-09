<?php

namespace App\Domain\ToDoList;

use Core\Models\BaseEntity;
use DateTime;

/**
 * ToDoList entity representing a task within a SAE group.
 * 
 * Tasks have a description, priority (1=High, 2=Medium, 3=Low),
 * completion status, and optional deadline.
 *
 * @category Domain
 * @package  App\Domain\ToDoList
 * @author   SAEManager Team
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoList extends BaseEntity
{
    private ?int $todoId;
    private int $saeGroupId;
    private string $description;
    private bool $checked;
    private int $priority;

    // New properties
    private ?DateTime $deadline = null;

    public function __construct(
        int $saeGroupId,
        string $description,
        int $priority = 2,
        bool $checked = false,
        ?int $todoId = null
        )
    {
        $this->saeGroupId = $saeGroupId;
        $this->description = $description;
        $this->priority = $priority;
        $this->checked = $checked;
        $this->todoId = $todoId;
    }

    // Business Logic
    
    /**
     * Marks the task as done/completed.
     *
     * @return void
     */
    public function markAsDone(): void
    {
        $this->checked = true;
    }

    /**
     * Marks the task as undone/incomplete.
     *
     * @return void
     */
    public function markAsUndone(): void
    {
        $this->checked = false;
    }

    /**
     * Checks if the task is overdue (past deadline and not completed).
     *
     * @return bool True if overdue, false otherwise.
     */
    public function isOverdue(): bool
    {
        if ($this->deadline === null) {
            return false;
        }
        $now = new DateTime();
        return $now > $this->deadline && !$this->checked;
    }

    /**
     * Checks if the task is urgent (priority 1).
     *
     * @return bool True if urgent, false otherwise.
     */
    public function isUrgent(): bool
    {
        return $this->priority === 1;
    }

    /**
     * Gets a human-readable priority label.
     *
     * @return string 'High', 'Medium', or 'Low'.
     */
    public function getPriorityLabel(): string
    {
        return match($this->priority) {
            1 => 'High',
            2 => 'Medium',
            3 => 'Low',
            default => 'Unknown'
        };
    }

    // Object Accessors
    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTime $deadline): void
    {
        $this->deadline = $deadline;
    }

    // Getters and Setters
    public function getTodoId(): ?int
    {
        return $this->todoId;
    }
    public function setTodoId(int $todoId): void
    {
        $this->todoId = $todoId;
    }

    public function getGroupId(): int
    {
        return $this->saeGroupId;
    }
    public function setGroupId(int $groupId): void
    {
        $this->saeGroupId = $groupId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
    public function setDescription(string $desc): void
    {
        $this->description = $desc;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
}