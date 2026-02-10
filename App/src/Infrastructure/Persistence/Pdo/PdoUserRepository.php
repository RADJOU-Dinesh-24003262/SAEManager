<?php

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\User\User;
use App\Domain\User\Student;
use App\Domain\User\Professor;
use App\Domain\User\Client;
use App\Domain\User\IRepository\IUserRepository;
use App\Domain\User\Exception\EmailAlreadyExistsException;
use Core\Database\Database;
use App\Infrastructure\Exception\PasswordUpdateException;
use PDO;
use PDOException;

/**
 * PDO implementation of the User repository.
 * 
 * Handles persistence for User entities (Student, Professor, Client) using PostgreSQL.
 *
 * @package App\Infrastructure\Persistence\Pdo
 */
class PdoUserRepository implements IUserRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Checks if a user exists with the given email.
     *
     * @param string $email The email to check (case-insensitive).
     * @return bool True if user exists, false otherwise.
     */
    public function existsByEmail(string $email): bool
    {
        try {
            $stmt = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE email = LOWER(:email)');
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        }
        catch (PDOException $e) {
            error_log('Erreur vérification email : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a user by email address.
     * 
     * Uses LEFT JOINs to fetch user type-specific data (student/professor/client)
     * in a single query for performance.
     *
     * @param string $email The user's email address (case-insensitive).
     * @return User|null The User entity (Student|Professor|Client) or null if not found.
     */
    public function findByEmail(string $email): ?User
    {
        try {
            // Use LEFT JOINs to fetch all possible subclass data in one query.
            // Aliasing potentially conflicting columns (like amu_id).
            $stmt = $this->connection->prepare(
                'SELECT u.*, 
                        s.student_id, s.major, s.year, s.td, s.tp, s.amu_id as student_amu_id,
                        p.professor_id, p.amu_id as professor_amu_id,
                        c.client_id, c.organisation
                 FROM users u
                 LEFT JOIN students s ON u.user_id = s.student_id
                 LEFT JOIN professors p ON u.user_id = p.professor_id
                 LEFT JOIN clients c ON u.user_id = c.client_id
                 WHERE u.email = LOWER(:email)'
            );
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }

            // Map aliased columns back to their expected property names
            if (!empty($data['student_amu_id'])) {
                $data['amu_id'] = $data['student_amu_id'];
            }
            elseif (!empty($data['professor_amu_id'])) {
                $data['amu_id'] = $data['professor_amu_id'];
            }

            $userType = match ((string)$data['user_type']) {
                    '0' => 'student',
                    '1' => 'professor',
                    '2' => 'client',
                    default => null,
                };

            if (!$userType) {
                return null;
            }

            // Instantiate the correct class. The parent constructor calls hydrate($data),
            // which populates properties matching the array keys.
            return match ($userType) {
                    'student' => new Student($data),
                    'professor' => new Professor($data),
                    'client' => new Client($data),
                };

        }
        catch (PDOException $e) {
            error_log('Error finding user by email: ' . $e->getMessage());
            return null;
        }
    }

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Saves a new user to the database.
     * 
     * Inserts into both the users table and the type-specific table
     * (students/professors/clients) within a transaction.
     *
     * @param User $user The user entity to save.
     * @return User The saved user entity with ID populated.
     * @throws EmailAlreadyExistsException If email already exists.
     * @throws PDOException If database operation fails.
     */
    public function save($user): User
    {
        try {
            $this->connection->beginTransaction();

            if ($this->existsByEmail($user->getEmail())) {
                throw new EmailAlreadyExistsException($user->getEmail());
            }

            $stmt = $this->connection->prepare(
                'INSERT INTO users (first_name, last_name, email, phone, hashed_password, user_type)
                VALUES (:first_name, :last_name, LOWER(:email), :phone, :hashed_password, :user_type)'
            );

            $stmt->execute([
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'hashed_password' => $user->getPasswordHash(),
                'user_type' => match ($user->getUserType()) {
                    'student' => '0',
                    'professor' => '1',
                    'client' => '2',
                    default => null,
                },
            ]);

            // Retrieve the ID
            $stmt = $this->connection->prepare('SELECT user_id FROM users WHERE email = LOWER(:email)');
            $stmt->execute(['email' => $user->getEmail()]);
            $userId = (int)$stmt->fetchColumn(0);
            $stmt->closeCursor();

            $user->setUserId($userId);

            $this->saveSpecificData($user);

            $this->connection->commit();
            return $user;
        }
        catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log('Erreur lors de la sauvegarde de l\'utilisateur : ' . $e->getMessage());
            throw $e;
        }
        catch (EmailAlreadyExistsException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    /**
     * Saves user type-specific data to the appropriate table.
     * 
     * Called internally by save() within a transaction.
     *
     * @param User $user The user entity (Student|Professor|Client).
     * @return void
     */
    private function saveSpecificData(User $user): void
    {
        if ($user instanceof Student) {
            $stmt = $this->connection->prepare(
                'INSERT INTO students (student_id, amu_id, year, td, tp, major)
                VALUES (:student_id, :amu_id, :year, :td, :tp, :major)'
            );
            $stmt->execute([
                'student_id' => $user->getUserId(),
                'amu_id' => $user->getAmuId(),
                'year' => $user->getYear(),
                'td' => $user->getTd(),
                'tp' => $user->getTp(),
                'major' => $user->getMajor()
            ]);
        }
        elseif ($user instanceof Professor) {
            $stmt = $this->connection->prepare(
                'INSERT INTO professors (professor_id, amu_id)
                VALUES (:professor_id, :amu_id)'
            );
            $stmt->execute([
                'professor_id' => $user->getUserId(),
                'amu_id' => $user->getAmuId()
            ]);
        }
        elseif ($user instanceof Client) {
            $stmt = $this->connection->prepare(
                'INSERT INTO clients (client_id, organisation)
                VALUES (:client_id, :organisation)'
            );
            $stmt->execute([
                'client_id' => $user->getUserId(),
                'organisation' => $user->getOrganisation()
            ]);
        }
    }

    /**
     * Updates the user's password.
     *
     * @param User $user The user entity with updated password hash.
     * @return void
     * @throws PasswordUpdateException If user not found or update fails.
     */
    public function updatePassword(User $user): void
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE users SET hashed_password = :password_hash WHERE email = LOWER(:email)'
            );

            $stmt->execute([
                'password_hash' => $user->getPasswordHash(),
                'email' => $user->getEmail(),
            ]);

            if ($stmt->rowCount() === 0) {
                if (!$this->existsByEmail($user->getEmail())) {
                    throw new PasswordUpdateException('Aucun utilisateur trouvé avec cet email.');
                }
            }
        }
        catch (PDOException $e) {
            error_log('Erreur mise à jour mot de passe : ' . $e->getMessage());
            throw new PasswordUpdateException('Erreur lors de la mise à jour du mot de passe.');
        }
    }

    /**
     * Deletes a user by email address.
     *
     * @param string $email The user's email address.
     * @return void
     * @throws PDOException If user not found or deletion fails.
     */
    public function deleteByEmail(string $email): void
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM users WHERE email = LOWER(:email)');
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() === 0) {
                throw new PDOException("User not found or already deleted.");
            }
        }
        catch (PDOException $e) {
            error_log('Erreur suppression du compte utilisateur : ' . $email . '. ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates user profile information.
     * 
     * Updates first_name, last_name, and phone. Email is used as identifier.
     *
     * @param User $user The user entity with updated data.
     * @return bool True if update was successful, false otherwise.
     * @throws PDOException If database operation fails.
     */
    public function update($user): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone
                 WHERE email = LOWER(:email)'
            );

            $stmt->execute([
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'phone' => $user->getPhone(),
                'email' => $user->getEmail()
            ]);

        // Note: If we need to update specific data (amu_id, etc), add logic here.
        return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log('Error updating user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets all clients with basic information.
     *
     * @return array<int, array<string, mixed>> Array of client data arrays.
     */
    public function getAllClients(): array
    {
        try {
            $stmt = $this->connection->query(
                'SELECT u.user_id, u.first_name, u.last_name, c.organisation 
                 FROM clients c 
                 JOIN users u ON c.client_id = u.user_id 
                 ORDER BY u.last_name, u.first_name'
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log('Error retrieving all clients: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all professors with basic information.
     *
     * @return array<int, array<string, mixed>> Array of professor data arrays.
     */
    public function getAllProfessors(): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.user_id, u.first_name, u.last_name, u.email, p.amu_id 
                 FROM professors p 
                 JOIN users u ON p.professor_id = u.user_id 
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log('Error retrieving all professors: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds a user by ID.
     *
     * @param int $id The user ID.
     * @return User|null The User entity or null if not found.
     */
    public function findById(int $id): ?User
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM users WHERE user_id = :user_id'
            );
            $stmt->execute(['user_id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Map aliased columns back to their expected property names
            if (!empty($user['student_amu_id'])) {
                $user['amu_id'] = $user['student_amu_id'];
            }
            elseif (!empty($user['professor_amu_id'])) {
                $user['amu_id'] = $user['professor_amu_id'];
            }

            $userType = match ((string)$user['user_type']) {
                    '0' => 'student',
                    '1' => 'professor',
                    '2' => 'client',
                    default => null,
                };

            if (!$userType) {
                return null;
            }

            // Instantiate the correct class. The parent constructor calls hydrate($data),
            // which populates properties matching the array keys.
            return match ($userType) {
                    'student' => new Student($user),
                    'professor' => new Professor($user),
                    'client' => new Client($user),
                };
        }
        catch (PDOException $e) {
            error_log('Error retrieving user by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Deletes a user by ID.
     *
     * @param int $id The user ID.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM users WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $id]);
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            return false;
        }
    }
}