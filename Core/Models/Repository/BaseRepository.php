<?php

namespace Core\Models\Repository;

use Core\includes\Database;
use Exception;
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
 * @template T of object
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
    #[\Override]
    public function findById(int $id)
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
     * Deletes an entity by its ID
     *
     * @param integer $id The ID of the entity to delete.
     * @return boolean True on success, false on failure.
     */
    #[\Override]
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

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in count in {$this->table}: " . $e->getMessage());
            return 0;
        }
    }
}
