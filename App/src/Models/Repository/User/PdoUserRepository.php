<?php

namespace Models\Repository\User;

use Core\Includes\Database;
use Core\Models\Repository\BaseRepository;
use Models\Entity\User\User;
use Models\Entity\User\Student;
use Models\Entity\User\Professor;
use Models\Entity\User\Client;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\InterfaceDB\RoleAccessInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of UserInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 * It implements the Interface defined in the Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<User>
 */
class PdoUserRepository extends BaseRepository implements UserInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->entityClass = User::class;
    }

    /**
     * Returns the primary key name.
     *
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'user_id';
    }




    /**
     * Finds a user by ID.
     *
     * @param integer $id The user ID.
     * @return User|null The user entity or null if not found.
     */
    #[Override]
    public function findById(int $id): ?User
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT users.*, 
                        students.td, students.tp, students.major, students.year,
                        clients.organisation,
                        COALESCE(students.amu_id, professors.amu_id) as amu_id
                FROM users
                LEFT JOIN students ON users.user_id = students.student_id
                LEFT JOIN professors ON users.user_id = professors.professor_id
                LEFT JOIN clients ON users.user_id = clients.client_id
                WHERE users.user_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }

            return $this->instantiateUser($data);
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds a user by email.
     *
     * @param string $email The user's email.
     * @return User|null The user entity or null if not found.
     */
    #[Override]
    public function findByEmail(string $email): ?User
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT users.*, 
                        students.td, students.tp, students.major, students.year,
                        clients.organisation,
                        COALESCE(students.amu_id, professors.amu_id) as amu_id
                FROM users
                LEFT JOIN students ON users.user_id = students.student_id
                LEFT JOIN professors ON users.user_id = professors.professor_id
                LEFT JOIN clients ON users.user_id = clients.client_id
                WHERE users.email = LOWER(:email)'
            );
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }

            return $this->instantiateUser($data);
        } catch (PDOException $e) {
            error_log("Error in findByEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Instantiates the correct User subclass based on data.
     *
     * @param array<string, mixed> $data The user data.
     * @return User
     */
    private function instantiateUser(array $data): User
    {
        return UserFactory::create($data);
    }

    /**
     * Inserts base user data into the database.
     *
     * @param object $user The user entity.
     * @return integer|boolean The id of the created user or false on failure.
     */
    #[Override]
    public function insert(object $user): int|bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $query = "INSERT INTO users (first_name, last_name, email, phone, hashed_password, user_type) 
                  VALUES (:first_name, :last_name, :email, :phone, :password, :user_type)";

        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':first_name', $user->getFirstName());
            $stmt->bindValue(':last_name', $user->getLastName());
            $stmt->bindValue(':email', $user->getEmail());
            $stmt->bindValue(':phone', $user->getPhone());
            $stmt->bindValue(':password', $user->getPasswordHash());
            $stmt->bindValue(':user_type', $user->getUserTypeCode());

            $stmt->execute();
            $id = (int) $this->connection->lastInsertId();
            $this->connection->commit();
            return $id;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Error in insert in users: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates user data.
     *
     * @param object $user The user entity.
     * @return boolean True on success.
     * @throws PDOException If a database error occurs.
     */
    public function update(object $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $query = "UPDATE users SET 
                  first_name = :first_name, 
                  last_name = :last_name, 
                  email = :email, 
                  phone = :phone, 
                  hashed_password = :password,
                  user_type = :user_type
                  WHERE user_id = :user_id";

        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':first_name', $user->getFirstName());
            $stmt->bindValue(':last_name', $user->getLastName());
            $stmt->bindValue(':email', $user->getEmail());
            $stmt->bindValue(':phone', $user->getPhone());
            $stmt->bindValue(':password', $user->getPasswordHash());
            $stmt->bindValue(':user_type', $user->getUserTypeCode());
            $stmt->bindValue(':user_id', $user->getUserId());

            $result = $stmt->execute();
            $this->connection->commit();

            return $result;
        } catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log("Error in update in users: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a user exists by email.
     *
     * @param string $email The email to check.
     * @return boolean True if exists, false otherwise.
     */
    public function existsByEmail(string $email): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM users WHERE email = LOWER(:email)'
            );
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error checking email existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a user's password.
     *
     * @param integer $userId       The user ID.
     * @param string  $passwordHash The new hashed password.
     * @return boolean True on success, false on failure.
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE users SET hashed_password = :password WHERE user_id = :user_id'
            );
            return $stmt->execute([
                'password' => $passwordHash,
                'user_id' => $userId,
            ]);
        } catch (PDOException $e) {
            error_log('Error updating password: ' . $e->getMessage());
            return false;
        }
    }
}
