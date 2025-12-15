<?php

namespace App\Models\ToDoList;

use Core\includes\Database;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use PDO;
use PDOException;

/**
 * Class ToDoList
 * This class contains functions to create and manage the task in to-do-list,
 * and handles communication with the database layer.
 *
 * @category    Models
 * @package     Src
 * @subpackages Models\ToDoList
 * @author      Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author      François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author      William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author      Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author      Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license     MIT License https://opensource.org/licenses/MIT
 * @link        https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoList
{
    /**
     * This is the id of the group of sae common for each student in it.
     *
     * @var integer $groupId
     */
    private int $groupId;

    /**
     * This is the id of the subject of sae
     * common for each student in the group of SAE.
     *
     * @var ?integer $sae_subject_id
     */
    private ?int $sae_subject_id;

    /**
     * This is the id of the to-do-list
     * common for each student in the group of SAE.
     *
     * @var integer $todo_id
     */
    private int $todo_id;

    /**
     * This is the content of the to-do-list.
     *
     * @var string $tododesc
     */
    private string $tododesc;

    /**
     * Creates an instance of the class
     *
     * This method constructs a user object with the data array given in parameters.
     *
     * @param array $data The data to make a todolist with.
     */
    private function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Creates an instance of the class.
     *
     * This method creates a user object with the data array given in parameters.
     * Use the connection of the database.
     *
     * @param array $data The data to make a todolist with.
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
     * @param  integer $groupId The id of the group of SAE.
     * @return void
     */
    public function setSaeGroupId(int $groupId): void
    {
        $this->$groupId = $groupId;
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
            'INSERT INTO sae_todolists(sae_group_id, tododesc, checked)
                                            VALUES (:sae_group_id, :tododesc, :checked)'
        );
        $stmt->execute(
            [
                'sae_group_id' => $this->groupId,
                'tododesc' => $this->tododesc,
                'checked' => false,
            ]
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
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

            if ($data) {
                $this->sae_subject_id = $data['sae_subject_id'];
                $this->todo_id = $data['todo_id'];
                $this->tododesc = $data['tododesc'];
                $this->groupId = $data['sae_group_id'];
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
}
