<?php

namespace Core\Models\Repository;

use Core\includes\Database;
use Core\Models\BaseModel;
use Core\Models\UseCase\InterfaceDB\RepositoryInterface;
use Exception;
use Override;
use PDO;
use PDOException;

/**
 * Base repository with common CRUD operations
 *
 * @category Model
 *
 * @package Src
 *
 * @subpackage Models/Repository
 *
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @template T of BaseModel
 * @implements RepositoryInterface<T>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The PDO connection instance
     * @var PDO
     */
    protected PDO $connection;

    /**
     * The table name associated with the repository
     * @var string
     */
    protected string $table;

    /** @var class-string<T> */
    protected string $entityClass;

    /**
     * @throws Exception If the connection to the database fail.
     */
    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Returns the name of the primary key
     *
     * @return string
     */
    abstract protected function getPrimaryKey(): string;

    /**
     * Finds an entity by its ID
     *
     * @param integer $id The ID of the entry to find in the database.
     * @return T|null Returns the entity if found, null otherwise
     */
    public function findById(int $id): ?BaseModel
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id"
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new $this->entityClass($data) : null;
        } catch (PDOException $e) {
            error_log("Error in findById in {$this->table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds all entities
     *
     * @return array<T> Returns an array of entities
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->query("SELECT * FROM {$this->table}");

            if ($stmt === false) {
                return [];
            }
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $entities = [];
            foreach ($results as $row) {
                $entities[] = new $this->entityClass($row);
            }

            return $entities;
        } catch (PDOException $e) {
            error_log("Error in findAll in {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Deletes an entity by its ID
     *
     * @param integer $id The ID of the entity to delete.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id"
            );
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in delete in {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Counts the total number of records
     *
     * @return integer The total number of records.
     * @throws PDOException If the query fails.
     */
    public function count(): int
    {
        try {
            $stmt = $this->connection->query("SELECT COUNT(*) FROM {$this->table}");

            if (!$stmt) {
                throw new PDOException("Failed to execute count query on {$this->table}");
            }

            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in count in {$this->table}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates data in the database.
     *
     * @param BaseModel $data The entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(BaseModel $data): bool
    {

        $attributes = $data->toArray();
        $query = "UPDATE {$this->table} SET ";
        foreach ($attributes as $key => $value) {
            if ($key === $this->getPrimaryKey()) {
                continue;
            }
            $query .= "{$key} = :{$key}, ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE {$this->getPrimaryKey()} = :{$this->getPrimaryKey()}";

        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute($data->toArray());
            $this->connection->commit();

            return $result;
        } catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log("Error in update in {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserts data into the database.
     *
     * @param BaseModel $data The entity to insert.
     * @return integer|boolean The ID of the inserted row or false on failure.
     */
    public function insert(BaseModel $data): int|bool
    {

        $attributes = $data->toArray();
        unset($attributes[$this->getPrimaryKey()]);
        $query = "INSERT INTO {$this->table} (";
        foreach ($attributes as $key => $value) {
            $query .= "{$key}, ";
        }
        $query = substr($query, 0, -2);

        $query .= ") VALUES (";
        foreach ($attributes as $key => $value) {
            $query .= ":{$key}, ";
        }
        $query = substr($query, 0, -2);
        $query .= ")";

        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($attributes);
            $id = (int)$this->connection->lastInsertId();
            $this->connection->commit();
            return $id;
        } catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log("Error in insert in {$this->table}: " . $e->getMessage());
            return false;
        }
    }
}
