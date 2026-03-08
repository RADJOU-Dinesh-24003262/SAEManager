<?php

namespace Models\Entity\User;

use Core\Models\BaseModel;
use Override;

/**
 * Abstract base class for all user types.
 *
 * Contains only business logic, no database operations.
 * Database operations are handled by Repositories.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class User extends BaseModel
{
    /**
     * The unique identifier of the user.
     *
     * @var integer
     */
    protected int $user_id;

    /**
     * The first name of the user.
     *
     * @var string
     */
    protected string $first_name;

    /**
     * The last name of the user.
     *
     * @var string
     */
    protected string $last_name;

    /**
     * The type of the user (student / professor / client).
     *
     * @var string
     */
    protected string $user_type;

    /**
     * The email of the user.
     *
     * @var string
     */
    protected string $email;

    /**
     * The hashed password of the user.
     *
     * @var string
     */
    protected string $hashed_password;

    /**
     * The phone number of the user.
     *
     * @var string
     */
    protected string $phone;

    // -----------------
    // Business Logic Methods
    // -----------------

    /**
     * Sets the hashed password for the user.
     *
     * @param string $password The plain text password.
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->hashed_password = password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verifies a password against the stored hash.
     *
     * @param string $password The plain text password to verify.
     * @return boolean True if password matches, false otherwise.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->hashed_password);
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the user's ID.
     *
     * @return integer
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Gets the user's first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * Gets the user's last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * Gets the user's full name.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Gets the user type.
     *
     * @return string
     */
    public function getUserType(): string
    {
        return $this->user_type;
    }

    /**
     * Gets the user's email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Gets the user's hashed password.
     *
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->hashed_password;
    }

    /**
     * Gets the user's phone number.
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    // -----------------
    // Setters
    // -----------------

    /**
     * Sets the user's ID.
     *
     * @param integer $userId The user ID.
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->user_id = $userId;
    }

    /**
     * Sets the user's phone number.
     *
     * @param string $phone The new phone number.
     * @return void
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Sets the user's first name.
     *
     * @param string $firstName The new first name.
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->first_name = $firstName;
    }

    /**
     * Sets the user's last name.
     *
     * @param string $lastName The new last name.
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->last_name = $lastName;
    }

    // -----------------
    // Type checking methods
    // -----------------

    /**
     * Checks if the user is a student.
     *
     * @return boolean
     */
    public function isStudent(): bool
    {
        return $this->user_type == 'student';
    }

    /**
     * Checks if the user is a professor.
     *
     * @return boolean
     */
    public function isProfessor(): bool
    {
        return $this->user_type == 'professor';
    }

    /**
     * Checks if the user is a client.
     *
     * @return boolean
     */
    public function isClient(): bool
    {
        return $this->user_type == 'client';
    }
    /**
     * Gets the user type code.
     *
     * @return string The user type code (0 for student, 1 for professor, 2 for client).
     */
    public function getUserTypeCode(): string
    {
        return match ($this->user_type) {
            'student' => '0',
            'professor' => '1',
            'client' => '2',
            default => '0',
        };
    }

    /**
     * Gets the user's role label for display.
     *
     * @return string
     */
    abstract public function getRoleLabel(): string;

    /**
     * Gets the user's dashboard meta information for display.
     *
     * @return array<string, mixed> A dictionary of label-value pairs.
     */
    abstract public function getDashboardMetaInfo(): array;

    /**
     * Get the ID of the entity.
     *
     * @return integer|null
     */
    #[Override]
    public function getId(): ?int
    {
        return $this->user_id ?? null;
    }
}
