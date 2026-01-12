<?php

namespace App\Models\ToDoList;

use Core\includes\Database;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\Models\BaseModel;
use PDO;
use PDOException;

/**
 * Class ToDoList
 * This class contains functions to create and manage the task in to-do-list,
 * and handles communication with the database layer.
 *
 * @category    Models
 * @package     Src
 * @subpackages Models/ToDoList
 * @author      Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author      François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author      William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author      Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author      Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license     MIT License https://opensource.org/licenses/MIT
 * @link        https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoList extends BaseModel
{
    /**
     * This is the id of the group of sae common for each student in it.
     *
     * @var integer $groupId
     */
    protected int $groupId;

    /**
     * This is the id of the subject of sae
     * common for each student in the group of SAE.
     *
     * @var ?integer $sae_subject_id
     */
    protected ?int $sae_subject_id;

    /**
     * This is the id of the to-do-list
     * common for each student in the group of SAE.
     *
     * @var integer $todo_id
     */
    protected int $todo_id;

    /**
     * This is the content of the to-do-list.
     *
     * @var string $tododesc
     */
    protected string $tododesc;

    /**
     * The checked status of the task.
     *
     * @var boolean $checked
     */
    protected bool $checked;

    /**
     * The priority of the task (1: High, 2: Medium, 3: Low).
     *
     * @var integer $priority
     */
    protected int $priority = 2;

    /**
     * Creates an instance of the class.
     *
     * This method creates a user object with the data array given in parameters.
     * Use the connection of the database.
     *
     * @param array<string, string|integer|null> $data The data to make a todolist with.
     *
     * @return self the new object.
     */
    public static function create(array $data = []): self
    {
        $todolist = new self($data);
        $todolist->save();
        return $todolist;
    }

    /**
     * @return integer the id of the todolist.
     */
    public function getTodoId(): int
    {
        return $this->todo_id;
    }

    /**
     * @return string the description of the task in the todolist.
     */
    public function getTodoDesc(): string
    {
        return $this->tododesc;
    }

    /**
     * @return integer the id of the group of SAE.
     */
    public function getSaeGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @return integer|null the id of the subject of the SAE.
     */
    public function getSaeSubjectId(): int|null
    {
        return $this->sae_subject_id;
    }

    /**
     * @return boolean the checked status of the task.
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * @return integer the priority of the task.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param  integer $groupId The id of the group of SAE.
     * @return void
     */
    public function setSaeGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @param  integer $sae_subject_id The subject of the group of SAE.
     * @return void
     */
    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    /**
     * @param  string $tododesc The description of the todolist.
     * @return void
     */
    public function setTododesc(string $tododesc): void
    {
        $this->tododesc = $tododesc;
    }

    /**
     * @param  integer $todo_id The id of the todoList.
     * @return void
     */
    public function setTodoId(int $todo_id): void
    {
        $this->todo_id = $todo_id;
    }

    /**
     * @param  boolean $checked The checked status of the task.
     * @return void
     */
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }

    /**
     * @param  integer $priority The priority of the task.
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Saves a todolist object into the database
     *
     * Tries to save a new todolist object into the database,
     * Only to be used for its creation. use another function for its update.
     *
     * @return boolean
     */
    public function save(): bool
    {
        $connection = database::getInstance();


        $stmt = $connection->prepare(
            'INSERT INTO sae_todolists(sae_group_id, tododesc, checked, priority)
                                            VALUES (:sae_group_id, :tododesc, :checked, :priority)'
        );
        return $stmt->execute(
            [
                'sae_group_id' => $this->groupId,
                'tododesc' => $this->tododesc,
                'checked' => false,
                'priority' => $this->priority
            ]
        );
    }

    /**
     * Fetches user data from the database using the id of the group of SAE.
     *
     * This method queries the database for a user record that matches the provided the id of the group.
     * If id is found, it populates the current object’s properties with the retrieved data,
     *
     * If no id is found or a database error occurs, an ExceptionFetchDataBD is thrown.
     *
     * @param string $sae_group_id The id of the group of sae to fetch.
     *
     * @return void
     *
     * @throws ExceptionFetchDataBD  If the id cannot be found or a database error occurs.
     */
    public function fetchDataFromDatabase(string $sae_group_id): void
    {
        try {
            $connection = database::getInstance();
            $stmt = $connection->prepare("SELECT * FROM sae_todolists WHERE  sae_group_id = :sae_group_id");
            $stmt->execute(['sae_group_id' => $sae_group_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($data)) {
                $this->sae_subject_id = isset($data['sae_subject_id']) ? (int) $data['sae_subject_id'] : null;
                $this->todo_id = (int) $data['todoid'];
                $this->tododesc = (string) $data['tododesc'];
                $this->groupId = (int) $data['sae_group_id'];
                $this->checked = (bool) $data['checked'];
                $this->priority = (int) $data['priority'];
            } else {
                throw new ExceptionFetchDataBD();
            }
        } catch (PDOException $e) {
            error_log("Erreur récupération de la todo-list : " . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Return the success of searching a todolist by id of group in the database.
     *
     * Attempts to find a todolist in the todolist relation in the database based on
     * their id of group of sae. If an id is found, returns true. False otherwise.
     *
     * @param integer $sae_group_id The id of group of sae to check for existence.
     *
     * @return boolean
     */
    public static function existBySaeGroupId(int $sae_group_id): bool
    {
        try {
            $connection = database::getInstance();
            $stmt = $connection->prepare("SELECT COUNT(*) FROM sae_todolists WHERE sae_group_id = :sae_group_id");
            $stmt->execute(['sae_group_id' => $sae_group_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur vérification de votre groupe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all tasks for a specific group.
     * Sorted by: Unchecked first, then by Priority (High=1 to Low=3), then by ID.
     *
     * @param integer $groupId The ID of the SAE group.
     * @return array<int, array<string, mixed>> List of tasks.
     */
    public static function getAllTasks(int $groupId): array
    {
        try {
            $connection = Database::getInstance();
            $stmt = $connection->prepare(
                'SELECT * FROM sae_todolists 
                 WHERE sae_group_id = :group_id 
                 ORDER BY checked ASC, priority ASC, todoid ASC'
            );
            $stmt->execute(['group_id' => $groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération des tâches : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the checked status of a specific task.
     *
     * @param integer $todoId  The ID of the to-do item.
     * @param boolean $checked The new checked status.
     * @return boolean True on success, false on failure.
     */
    public static function updateCheckedStatus(int $todoId, bool $checked): bool
    {

        try {
            $connection = Database::getInstance();
            $stmt = $connection->prepare(
                'UPDATE sae_todolists SET checked = :checked WHERE todoid = :todo_id'
            );
            $stmt->execute([
                'checked' => (int) $checked,
                'todo_id' => $todoId
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur mise à jour statut tâche : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the priority of a specific task.
     *
     * @param integer $todoId   The ID of the to-do item.
     * @param integer $priority The new priority level.
     * @return boolean True on success, false on failure.
     */
    public static function updatePriority(int $todoId, int $priority): bool
    {
        try {
            $connection = Database::getInstance();
            $stmt = $connection->prepare(
                'UPDATE sae_todolists SET priority = :priority WHERE todoid = :todo_id'
            );
            $stmt->execute([
                'priority' => $priority,
                'todo_id' => $todoId
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur mise à jour priorité tâche : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new task for a specific group with optional priority.
     *
     * @param integer $groupId     The ID of the SAE group.
     * @param string  $description The description of the new task.
     * @param integer $priority    The priority of the task.
     * @return integer|false The ID of the new task on success, false on failure.
     */
    public static function createTask(int $groupId, string $description, int $priority = 2): int|false
    {
        try {
            $connection = Database::getInstance();
            $stmt = $connection->prepare(
                'INSERT INTO sae_todolists(sae_group_id, tododesc, checked, priority)
                 VALUES (:sae_group_id, :tododesc, FALSE, :priority)'
            );
            $stmt->execute([
                'sae_group_id' => $groupId,
                'tododesc' => $description,
                'priority' => $priority
            ]);

            // Return the ID of the newly inserted task.
            return (int) $connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur création nouvelle tâche : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a task by ID.
     *
     * @param integer $todoId The ID of the to-do item to delete.
     * @return boolean True on success, false on failure.
     */
    public static function deleteTask(int $todoId): bool
    {
        try {
            $connection = Database::getInstance();
            $stmt = $connection->prepare('DELETE FROM sae_todolists WHERE todoid = :todo_id');
            $stmt->execute(['todo_id' => $todoId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur suppression tâche : " . $e->getMessage());
            return false;
        }
    }
}
