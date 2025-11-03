<?php

namespace Models\User;

use Core\includes\Database;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\includes\exception\ExceptionPasswordUpdateFailed;
use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use PDO;
use PDOException;

/**
 * Abstract base class for all user types.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\User
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class User
{
    /**
     * The first name of the user.
     *
     * @var string
     */
    protected string $first_name = '';

    /**
     * The last name of the user.
     *
     * @var string
     */
    protected string $last_name = '';

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
    protected string $email = '';

    /**
     * The hashed password of the user.
     *
     * @var string
     */
    protected string $hashed_password = '';

    /**
     * The phone number of the user.
     *
     * @var string
     */
    protected string $phone = '';

    /**
     * Initializes a new User instance with optional data.
     *
     * @param array $data Optional data to initialize the user with.
     */
    protected function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['password', 'passwordverif', 'terms'], true)) {
                continue;
            }

            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Factory method to create the appropriate user type from registration data.
     *
     * @param array $data The registration data.
     *
     * @return User The created user instance.
     * @throws \InvalidArgumentException If user is not valid.
     */
    public static function createFromRegistrationData(array $data): User
    {
        $userType = $data['user_type'] ?? '';

        $user = match ($userType) {
            'student' => new Student($data),
            'professor' => new Professor($data),
            'client' => new Client($data),
            default => throw new \InvalidArgumentException("Type d'utilisateur invalide : {$userType}"),
        };

        $user->setPassword($data['password']);
        return $user;
    }

    /**
     * Factory method to create a user from login data.
     *
     * @param array $data The login credentials.
     *
     * @return User The authenticated user instance.
     *
     * @throws ExceptionValidationLogin If authentication fails.
     * @throws ExceptionFetchDataBD     If fetching user data fails.
     */
    public static function createFromLoginData(array $data): User
    {
        $connection = Database::getInstance();
        $stmt = $connection->prepare(
            'SELECT email, hashed_password, user_type FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $data['email']]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || !password_verify($data['password'], $result['hashed_password'])) {
            throw new ExceptionValidationLogin();
        }


        $user = match ((string) $result['user_type']) {
            '0' => new Student($data),
            '1' => new Professor($data),
            '2' => new Client($data),
            default => throw new ExceptionFetchDataBD(),
        };

        $user->fetchData($data['email']);
        return $user;
    }


    /**
     * Sets the hashed password for the user.
     *
     * @param string $password The plain text password.
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Saves the user to the database.
     * Template method - calls saveSpecificData() for type-specific logic.
     *
     * @return void
     */
    public function save(): void
    {
        $connection = Database::getInstance();

        $stmt = $connection->prepare(
            'INSERT INTO users (first_name, last_name, email, phone, hashed_password, user_type)
             VALUES (:first_name, :last_name, :email, :phone, :hashed_password, :user_type)'
        );

        $stmt->execute(
            [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'hashed_password' => $this->hashed_password,
            'user_type' => match ($this->user_type) {
                'student'   => '0',
                'professor' => '1',
                'client'    => '2',
                default     => null, // If there something that is unusual.
            },
            ]
        );

        $stmt = $connection->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->execute(['email' => $this->email]);
        $userId = (int) $stmt->fetchColumn(0);

        $this->saveSpecificData($connection, $userId);
    }

    /**
     * Abstract method to save type-specific data.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
     */
    abstract protected function saveSpecificData(PDO $connection, int $userId): void;

    /**
     * Fetches user data from the database.
     * Template method - calls fetchSpecificData() for type-specific logic.
     *
     * @param string $email The user's email.
     *
     * @return void
     *
     * @throws ExceptionFetchDataBD If data retrieval fails.
     */
    public function fetchData(string $email): void
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($data)) {
                throw new ExceptionFetchDataBD();
            }

            foreach ($data as $key => $value) {
                if ($key === 'user_type') {
                    $this->user_type = match ((string) $data['user_type']) {
                        '0' => 'student',
                        '1' => 'professor',
                        '2' => 'client',
                        default => 'client'
                    };
                } elseif (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }

            $this->fetchSpecificData($db, $email);
        } catch (PDOException $e) {
            error_log('Erreur récupération données utilisateur : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Abstract method to fetch type-specific data.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user's email.
     *
     * @return void
     */
    abstract protected function fetchSpecificData(PDO $db, string $email): void;

    /**
     * Checks if a user exists by email.
     *
     * @param string $email The email to check.
     *
     * @return boolean True if the user exists, false otherwise.
     */
    public static function existsByEmail(string $email): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification email : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a user's password.
     *
     * @param string $email       The user's email.
     * @param string $newPassword The new password.
     *
     * @return void
     *
     * @throws ExceptionPasswordUpdateFailed If the password update fails.
     */
    public static function updatePasswordByEmail(string $email, string $newPassword): void
    {
        try {
            $db = Database::getInstance();
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $db->prepare(
                'UPDATE users SET hashed_password = :password_hash WHERE email = :email'
            );

            $stmt->execute(
                [
                'password_hash' => $hashed_password,
                'email' => $email,
                ]
            );

            if ($stmt->rowCount() === 0) {
                throw new ExceptionPasswordUpdateFailed('Aucun utilisateur trouvé avec cet email.');
            }
        } catch (PDOException $e) {
            error_log('Erreur mise à jour mot de passe : ' . $e->getMessage());
            throw new ExceptionPasswordUpdateFailed('Erreur lors de la mise à jour du mot de passe.');
        }
    }

    /**
     * Abstract method to fetch the SAE infos from the Database
     * @param PDO $connection   The database connection
     * @param int $userId       The user's id
     *
     * @return void
     */
    abstract protected function fetchSAEData(PDO $connection, int $userId): array;

    // -----------------
    // Getters
    // -----------------

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
    // Type checking methods
    // -----------------

    /**
     * Checks if the user is a student.
     *
     * @return boolean
     */
    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }

    /**
     * Checks if the user is a professor.
     *
     * @return boolean
     */
    public function isProfessor(): bool
    {
        return $this->user_type === 'professor';
    }

    /**
     * Checks if the user is a client.
     *
     * @return boolean
     */
    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }
}
