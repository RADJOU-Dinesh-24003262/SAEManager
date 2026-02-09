<?php

namespace App\Domain\User;

use Core\Models\BaseEntity;
use InvalidArgumentException;

/**
 * Abstract base class for all user types in the Domain.
 * 
 * Provides common properties and methods for Student, Professor, and Client.
 * Uses factory pattern for creating specific user types from registration data.
 *
 * @category Domain
 * @package  App\Domain\User
 * @author   RADJOU Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class User extends BaseEntity
{
    protected int $user_id;
    protected string $first_name = '';
    protected string $last_name = '';
    protected string $email = '';
    protected string $hashed_password = '';
    protected string $phone = '';

    /**
     * Factory method to create the appropriate user type from registration data.
     *
     * @param array $data Registration data including 'user_type' and 'password'.
     * @return User The created user instance (Student, Professor, or Client).
     * @throws InvalidArgumentException If user_type is invalid.
     */
    public static function createFromRegistrationData(array $data): User
    {
        $userType = $data['user_type'] ?? '';

        $user = match ($userType) {
                'student' => new Student($data),
                'professor' => new Professor($data),
                'client' => new Client($data),
                default => throw new InvalidArgumentException("Type d'utilisateur invalide : {$userType}"),
            };

        $user->setPassword($data['password']);
        $user->addDomainNameToEmail();

        return $user;
    }

    /**
     * Sets the user password (hashed with ARGON2ID).
     *
     * @param string $password The plain text password.
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->hashed_password = password_hash($password, PASSWORD_ARGON2ID);
    }


    /**
     * Gets the user's identity (full name).
     *
     * @return string The full name (first name + last name).
     */
    public function getIdentity(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }


    /**
     * Verifies a password against the stored hash.
     *
     * @param string $password The plain text password to verify.
     * @return bool True if password matches, false otherwise.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->hashed_password);
    }

    /**
     * Adds the appropriate AMU email domain based on user type.
     * Students get @etu.univ-amu.fr, professors get @univ-amu.fr.
     *
     * @return void
     */
    public function addDomainNameToEmail(): void
    {
        if (str_contains($this->email, '@')) {
            return;
        }

        if ($this->isStudent()) {
            $this->email .= '@etu.univ-amu.fr';
        }
        elseif ($this->isProfessor()) {
            $this->email .= '@univ-amu.fr';
        }
    }

    // Getters
    public function getFirstName(): string
    {
        return $this->first_name;
    }
    public function getLastName(): string
    {
        return $this->last_name;
    }
    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPasswordHash(): string
    {
        return $this->hashed_password;
    }
    public function getPhone(): string
    {
        return $this->phone;
    }
    public function getUserId(): int
    {
        return $this->user_id ?? 0;
    }

    public function setUserId(int $id): void
    {
        $this->user_id = $id;
    }
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    // Type checking
    public function isStudent(): bool
    {
        return $this instanceof Student;
    }
    public function isProfessor(): bool
    {
        return $this instanceof Professor;
    }
    public function isClient(): bool
    {
        return $this instanceof Client;
    }
    public function getUserType(): ?string
    {
        if ($this instanceof Student) {
            return 'student';
        }
        if ($this instanceof Professor) {
            return 'professor';
        }
        if ($this instanceof Client) {
            return 'client';
        }
        return null;
    }
}