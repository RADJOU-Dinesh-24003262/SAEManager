<?php

namespace Models\Entity\ToDoItem;

use Core\Models\BaseModel;
use Override;

/**
 * Represents a to-do list item in the system.
 *
 * Contains only business logic, no database operations.
 * Database operations are handled by ToDoListRepository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoItem extends BaseModel
{
    /**
     * The unique identifier of the to-do item.
     *
     * @var integer
     */
    protected int $todoid;

    /**
     * The SAE subject ID this to-do belongs to.
     *
     * @var integer
     */
    protected int $sae_subject_id;

    /**
     * The SAE group ID this to-do belongs to.
     *
     * @var integer
     */
    protected int $sae_group_id;

    /**
     * The description of the to-do item.
     *
     * @var string
     */
    protected string $tododesc;

    /**
     * Whether the to-do is checked/completed.
     *
     * @var boolean
     */
    protected bool $checked = false;

    /**
     * The priority of the to-do (1=High, 2=Medium, 3=Low).
     *
     * @var integer
     */
    protected int $priority = 2; // Default: Medium.

    /**
     * The end date of the task
     *
     * @var null|string
     */
    protected ?string $end_date = null;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $data Initial data.
     */
    public function __construct(array $data = [])
    {
        if (isset($data['groupId']) && !isset($data['sae_group_id'])) {
            $data['sae_group_id'] = $data['groupId'];
            unset($data['groupId']);
        }

        parent::__construct($data);
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the to-do ID.
     *
     * @return integer
     */
    public function getTodoId(): int
    {
        return $this->todoid;
    }

    /**
     * Gets the SAE subject ID.
     *
     * @return integer
     */
    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    /**
     * Gets the SAE group ID.
     *
     * @return integer
     */
    public function getSaeGroupId(): int
    {
        return $this->sae_group_id;
    }

    /**
     * Gets the to-do description.
     *
     * @return string
     */
    public function getTodoDesc(): string
    {
        return $this->tododesc;
    }

    /**
     * Checks if the to-do is checked/completed.
     *
     * @return boolean
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * Gets the priority.
     *
     * @return integer
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Gets the end date of the task
     *
     * @return null|string
     */
    public function getEndDate(): ?string
    {
        return $this->end_date ?? null;
    }

    // -----------------
    // Setters
    // -----------------

    /**
     * Sets the to-do ID.
     *
     * @param integer $todoId The to-do ID.
     * @return void
     */
    public function setTodoId(int $todoId): void
    {
        $this->todoid = $todoId;
    }

    /**
     * Sets the SAE subject ID.
     *
     * @param integer $saeSubjectId The SAE subject ID.
     * @return void
     */
    public function setSaeSubjectId(int $saeSubjectId): void
    {
        $this->sae_subject_id = $saeSubjectId;
    }

    /**
     * Sets the SAE group ID.
     *
     * @param integer $saeGroupId The SAE group ID.
     * @return void
     */
    public function setSaeGroupId(int $saeGroupId): void
    {
        $this->sae_group_id = $saeGroupId;
    }

    /**
     * Sets the to-do description.
     *
     * @param string $tododesc The description.
     * @return void
     */
    public function setTododesc(string $tododesc): void
    {
        $this->tododesc = $tododesc;
    }

    /**
     * Sets the checked status.
     *
     * @param boolean $checked Whether the to-do is checked.
     * @return void
     */
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }

    /**
     * Sets the priority.
     *
     * @param integer $priority The priority (1=High, 2=Medium, 3=Low).
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Sets the end date of the task
     *
     * @param string|null $endDate The end date of the task.
     * @return void
     */
    public function setEndDate(?string $endDate): void
    {
        $this->end_date = $endDate;
    }

    /**
     * Get the ID of the entity.
     *
     * @return integer|null
     */
    #[Override]
    public function getId(): ?int
    {
        return $this->todoid ?? null;
    }
}
