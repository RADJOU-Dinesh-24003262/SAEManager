<?php

namespace Models\User;

use includes\database;
use includes\exception\ExceptionFetchDataBD;
use includes\exception\ExceptionPasswordUpdateFailed;
use PDO;
use PDOException;
use includes\exception\ExceptionValidationLogin;

/**
 * Class User
 
 * @package     src

 * @subpackage  Models\User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class regroup function to create users and make relation with the database
 */
class User
{
    /**
     * The amU identification string
     * @var string
     */
    private string $amuId = '';
    /**
     * The first name of the user
     * @var string
     */
    private string $firstName = '';
    /**
     * The last name of the user
     * @var string
     */
    private string $lastName = '';
    /**
     * The gender of the user
     * @var string
     */
    private string $gender = '';
    /**
     * The type of the user (student / personnel...)
     * @var string
     */
    private string $userType = '';
    /**
     * The email of the user
     * @var string
     */
    private string $email = '';
    /**
     * The passwordHash of the user
     * @var string
     */
    private string $passwordHash = '';
    /**
     * The phone number of the user
     * @var string
     */
    private string $phone = '';
    /**
     * The date of birth of the user
     * @var string
     */
    private string $dateOfBirth = '';
    /**
     * The city of study of the user
     * @var string
     */
    private string $city = '';
    /**
     * The year of study of the user
     * @var string
     */
    private ?int $year = null;
    /**
     * The major of the user
     * @var string
     */
    private ?string $parcours = null;
    /**
     * The sub-group of the user
     * @var string
     */
    private ?string $td = null;
    /**
     * The sub-sub-group of the user
     * @var string
     */
    private ?string $tp = null;

    /**
     * Creates an instance of the class
     * 
     * This method constructs a user object with the data array given in parametters.
     * The integrity of the array should have been checked earlier in the user creation process.
     * 
     * @param array $data The data to make a user with
     */
    private function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                continue; // Skip password, use setPassword method instead
            }
            $this->$key = $value;
        }
    }

    /**
     * Creates an instance of the class
     * 
     * This method creates a user object with the data array given in parametters.
     * If no password are set, the new object password field is filled with the inputed registration password.
     * 
     * @param array $data The data to make a user with
     * 
     * @return self the new object.
     */
    public static function createFromRegistrationData(array $data): self
    {
        $user = new self($data);
        $user->setPassword($data['password']);
        return $user;
    }

    /**
     * Creates an instance of the class
     * 
     * This method creates a user object with the data array which should be login credentials.
     * uses the conection to the database.
     * 
     * @param array $data The data to make a user with
     * 
     * @return self the new object.
     */
    public static function createFromLoginData(array $data): self
    {
        $user = new self($data);
        $user->login($user->email, $data['password']);
        $user->fetchDataFromDatabase($data['email']);
        return $user;
    }

    /**
     * 
     * Sets the password_hash field to the current user.
     * To be used for security
     * 
     * @param string $password The password to hash 
     * 
     * @return void
     */
    public function setPassword(string $password): void
    {

        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Returns the success of fetching a user in the database
     * 
     * Tries to fetch a user in the database depending on it's user type.
     * Returns the success of this action.
     *  
     * @return boolean
     */
    public function save(): bool
    {
        $connection = database::getInstance();

        if ($this->userType === 'student') {
            $stmt = $connection->prepare("
            SELECT * FROM register_student(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId, 
                :parcours, 
                :year, 
                :td, 
                :tp
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city,
                'amuId' => $this->amuId,
                'parcours' => $this->parcours,
                'year' => $this->year,
                'td' => $this->td,
                'tp' => $this->tp
            ]);
        } elseif ($this->userType === 'professor') {
            $stmt = $connection->prepare("
            SELECT * FROM register_teacher(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city,
                'amuId' => $this->amuId
            ]);
        } else {
            $stmt = $connection->prepare("
            SELECT * FROM register_user(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city
            ]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
    }

    /**
     * 
     * Attempts to log a user using the credentials given in
     * parametters.
     * 
     * @param string $email
     * @param string $password
     *  
     * @return void
     */
    public function login(string $email, string $password): void
    {

        $connection = database::getInstance();
        $stmt = $connection->prepare("SELECT connection(?)");
        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $composite = trim($row['connection'], '()');
        $parts = explode(',', $composite);

        $user_id = $parts[0];
        $passwordHash = $parts[1];
        $success = ($parts[2] === 't'); // PostgreSQL boolean: 't' = true, 'f' = false

        if (!($success === true && password_verify($password, $passwordHash))) {
            throw new ExceptionValidationLogin();
        }
    }

    /**
     * Fetches user data from the database using their email address.
     *
     * This method queries the database for a user record that matches the provided email address. 
     * If a user is found, it populates the current object’s properties with the retrieved data, 
     * including personal information and, if applicable, student-specific details.
     *
     * If no user is found or a database error occurs, an ExceptionFetchDataBD is thrown.
     *
     * @param string $email  The email address of the user to fetch.
     *
     * @return void
     *
     * @throws ExceptionFetchDataBD  If the user cannot be found or a database error occurs.
     */
    public function fetchDataFromDatabase(string $email): void
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->amuId = $data['amuid'] ?? $data['amuid2'] ?? '';
                $this->firstName = $data['first_name'];
                $this->lastName = $data['last_name'];
                $this->userType = $data['amuid'] ? 'Student' : ($data['amuid2'] ? 'Professor' : 'Client');
                $this->email = $data['email'];
                $this->passwordHash = $data['password'];
                $this->phone = $data['phone'];
                $this->dateOfBirth = $data['dateofbirth'];
                $this->city = $data['city'];
                if ($this->isStudent()) {
                    $stmt = $db->prepare("SELECT * FROM student WHERE amuid = :amuid");
                    $stmt->execute(['amuid' => $this->amuId]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);

                    $this->year = (int)$data['year'];
                    $this->parcours = $this->year !== 1 ? $data['parcours'] : null;
                    $this->td = $data['td'];
                    $this->tp = $data['tp'];
                }
            } else {
                throw new ExceptionFetchDataBD();
            }
        } catch (PDOException $e) {
            error_log("Erreur récupération données utilisateur: " . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Return the success of searching a user by email in the database
     * 
     * Attempts to find a user in the user relation in the database based on
     * their email. If a user is found, returns true. False otherwise.
     * 
     * @param string $email
     *  
     * @return boolean
     */
    public static function existsByEmail(string $email): bool
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 
     * Attempts to update a user's password based on it's email
     * 
     * @param string $email       The email of the user to update the password of.
     * @param string $newPassword The new password to be updated.
     *  
     * @return void
     */
    public static function updatePasswordByEmail(string $email, string $newPassword): void
    {
        try {
            $db = database::getInstance();
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password_hash WHERE email = :email");
            if ($stmt->execute(['password_hash' => $passwordHash, 'email' => $email]) && $stmt->rowCount() === 0) {
                throw new ExceptionPasswordUpdateFailed("Aucun utilisateur trouvé avec cet email.");
            }
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour mot de passe: " . $e->getMessage());
            throw new ExceptionPasswordUpdateFailed("Erreur lors de la mise à jour du mot de passe.");
        }
    }

    // Getters

    /**
     * Returns the amUID of the user.
     * 
     * @return string the amUID of the user.
     */
    public function getAmuId(): string { return $this->amuId; }
    /**
     * Returns the first name of the user.
     * 
     * @return string the first name of the user.
     */
    public function getFirstName(): string { return $this->firstName; }
    /**
     * Returns the last name of the user.
     * 
     * @return string the last name of the user.
     */
    public function getLastName(): string { return $this->lastName; }
    /**
     * Returns the full name of the user. As [firstName]+[lastName]
     * 
     * @return string the full name of the user.
     */
    public function getFullName(): string { return $this->lastName . ' ' . $this->firstName; }
    /**
     * Returns the user type of the user.
     * 
     * @return string the user type of the user.
     */
    public function getUserType(): string { return $this->userType; }
    /**
     * Returns the email of the user.
     * 
     * @return string the email of the user.
     */
    public function getEmail(): string { return $this->email; }
    /**
     * Returns the hashed password number of the user.
     * 
     * @return string the hashed password number of the user.
     */
    public function getPasswordHash(): string { return $this->passwordHash; }
    /**
     * Returns the phone number of the user.
     * 
     * @return string the phone number of the user.
     */
    public function getPhone(): string { return $this->phone; }
    /**
     * Returns the date of birth of the user.
     * 
     * @return string the date of birth of the user.
     */
    public function getDateOfBirth(): string { return $this->dateOfBirth; }
    /**
     * Returns the city of study of the user.
     * 
     * @return string the city of study of the user.
     */
    public function getCity(): string { return $this->city; }
    /**
     * Returns the year of study of the user.
     * 
     * @return int the year of study of the user.
     */
    public function getYear(): ?int { return (int) $this->year; }
    /**
     * Returns the major of the user.
     * 
     * @return string the major of the user.
     */
    public function getParcours(): ?string { return $this->parcours; }
    /**
     * Returns the sub-group of the user.
     * 
     * @return string the sub-group of the user.
     */
    public function getTd(): ?string { return $this->td; }
    /**
     * Returns the sub-sub-group of the user.
     * 
     * @return string the sub-sub-group of the user.
     */
    public function getTp(): ?string { return $this->tp; }

    /**
     * Returns true if the user is a student.
     * 
     * @return boolean is the user a student?
     */
    public function isStudent(): bool { return $this->userType === 'student'; }
    /**
     * Returns true if the user is a professor.
     * 
     * @return boolean is the user a professor?
     */
    public function isProfessor(): bool { return $this->userType === 'professor'; }
    /**
     * Returns true if the user is a client.
     * 
     * @return boolean is the user a companie?
     */
    public function isClient(): bool { return $this->userType === 'client'; }
}
