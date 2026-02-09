<?php

namespace App\Domain\SAE;

use App\Domain\User\Professor;
use App\Domain\User\Student;
use App\Domain\ToDoList\ToDoList;
use Core\Models\BaseEntity;

class SaeGroup extends BaseEntity
{
    private ?int $id;
    private ?int $saeId;
    private ?int $professorId;

    // Object references
    private ?Professor $supervisor = null;
    /** @var Student[] */
    private array $students = [];
    /** @var ToDoList[] */
    private array $todoLists = [];

    public function __construct(
        int $saeId,
        ?int $professorId = null,
        ?int $id = null
        )
    {
        $this->professorId = $professorId;
        $this->id = $id;
        $this->saeId = $saeId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getSaeId(): ?int
    {
        return $this->saeId;
    }
    public function setSaeId(?int $saeId): void
    {
        $this->saeId = $saeId;
    }

    public function getProfessorId(): ?int
    {
        return $this->professorId;
    }
    public function setProfessorId(?int $professorId): void
    {
        $this->professorId = $professorId;
    }

    // Object Accessors
    public function getSupervisor(): ?Professor
    {
        return $this->supervisor;
    }

    public function setSupervisor(?Professor $supervisor): void
    {
        $this->supervisor = $supervisor;
    // $this->professorId = $supervisor ? $supervisor->getId() : null;
    }

    /**
     * @return Student[]
     */
    public function getStudents(): array
    {
        return $this->students;
    }

    public function addStudent(Student $student): void
    {
        if (!in_array($student, $this->students, true)) {
            $this->students[] = $student;
        }
    }

    public function removeStudent(Student $student): void
    {
        $key = array_search($student, $this->students, true);
        if ($key !== false) {
            unset($this->students[$key]);
            $this->students = array_values($this->students); // Reindex
        }
    }

    /**
     * @return ToDoList[]
     */
    public function getTodoLists(): array
    {
        return $this->todoLists;
    }

    public function addToDoList(ToDoList $toDoList): void
    {
        $this->todoLists[] = $toDoList;
    }
}