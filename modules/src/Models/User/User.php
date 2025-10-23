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
 * This class contains functions to create and manage users,
 * and handles communication with the database layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\User
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
*/

class User
{
    /**
     * The amU identification string
     *
     * @var string
     */
    private string $amu_id = '';
    /**
     * The first name of the user
     *
     * @var string
     */
    private string $first_name = '';
    /**
     * The last name of the user
     *
     * @var string
     */
    private string $last_name = '';
    /**
     * The gender of the user
     *
     * @var string
     */
    private string $gender = '';
    /**
     * The type of the user (student / personnel...)
     *
     * @var string
     */
    private string $user_type = '';
    /**
     * The email of the user
     *
     * @var string
     */
    private string $email = '';
    /**
     * The passwordHash of the user
     *
     * @var string
     */
    private string $hashed_password = '';
    /**
     * The phone number of the user
     *
     * @var string
     */
    private string $phone = '';
    /**
     * The date of birth of the user
     *
     * @var string
     */
    private string $dateOfBirth = '';
    /**
     * The city of study of the user
     *
     * @var string
     */
    private string $city = '';
    /**
     * The year of study of the user.
     *
     * @var integer|null
     */
    private ?int $year = null;
    /**
     * The major of the user
     *
     * @var string
     */
    private ?string $parcours = null;
    /**
     * The sub-group of the user
     *
     * @var string
     */
    private ?string $td = null;
    /**
     * The sub-sub-group of the user
     *
     * @var string
     */
    private ?string $tp = null;
    /**
     * The organisation of the client if the user is one.
     * @var string
     */
    private ?string $organisation = null;
    /**
     * Creates an instance of the class
     *
     * This method constructs a user object with the data array given in parametters.
     * The integrity of the array should have been checked earlier in the user creation process.
     *
     * @param array $data The data to make a user with.
     */
    private function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if ($key === 'password' || $key === 'passwordverif' || $key === 'terms') {
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
     * @param array $data The data to make a user with.
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
     * @param array $data The data to make a user with.
     *
     * @return self the new object.
     */
    public static function createFromLoginData(array $data): self
    {
        $user = new self($data);
        $user->login($user->email, $data['password']);
        $user->fetchData($data['email']);
        return $user;
    }
    /**
     * Sets the password_hash field to the current user.
     * To be used for security
     *
     * @param string $password The password to hash.
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }
    /**
     *
     * Tries to fetch a user in the database depending on it's user type.
     *
     * @return void
     */
    public function save(): void
    {
        $connection = database::getInstance();
        //Create the user in the user relation in the database
        $stmt = $connection->prepare(
        "INSERT INTO users(
            first_name,
            last_name,
            email,
            phone,
            hashed_password)
            VALUES(
            :first_name,
            :last_name,
            :email,
            :phone,
            :hashed_password)
        ");
        $stmt->execute([
                'email' => $this->email,
                'last_name' => $this->last_name,
                'first_name' => $this->first_name,
                'hashed_password' => $this->hashed_password,
                'phone' => $this->phone
        ]);
        $stmt = $connection->prepare("SELECT user_id FROM users WHERE email=:email");
        $stmt->execute(
            [
                'email' => $this->email
            ]
        );
        $temp_id = '';
        $temp_id = $stmt->fetchColumn(0);
        if ($this->user_type === 'student') {
            /* Relics from the past (depreciated)

        if ($this->userType === 'student') {
            $stmt = $connection->prepare(
                "
            SELECT * FROM register_student(
                :email, 
                :lastName, 
                :first_name, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId, 
                :parcours, 
                :year, 
                :td, 
                :tp
            )*/
            //Create the user in the student relation in the database
            $stmt = $connection->prepare(
            "INSERT INTO students(
                student_id,
                amu_id,
                year,
                td,
                tp)
                VALUES(
                :student_id,
                :amu_id,
                :year,
                :td,
                :tp)
            ");
            $stmt->execute(
                [
                    'student_id' => $temp_id,
                    'amu_id' => $this->amu_id,
                    'year' => $this->year,
                    'td' => $this->td,
                    'tp' => $this->tp
                ]
            );
        }
        elseif ($this->user_type === 'professor') {
            $stmt = $connection->prepare(/*"
            SELECT * FROM register_teacher(
                :email, 
                :lastName, 
                :first_name, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId
            )
            "*/
            "INSERT INTO professors(
                professor_id,
                amu_id)
                VALUES(
                :professor_id,
                :amu_id)
            "
            );
            $stmt->execute(
                [
                    'professor_id' => $temp_id,
                    'amu_id' => $this->amu_id
                ]
            );
        }
        else {
            $stmt = $connection->prepare(/*"
        "
            );
            $stmt->execute(
                [
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city,
                'amuId' => $this->amuId
                ]
            );
        } else {
            $stmt = $connection->prepare(
                "
            SELECT * FROM register_user(
                :email, 
                :lastName, 
                :first_name, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city
            )
            "*/
            "INSERT INTO clients(
                client_id,
                organisation)
                VALUES(
                :client_id,
                :organisation)
            ");
            $stmt->execute(
                [
                    'client_id' => $temp_id,
                    'organisation' => $this->organisation
                ]
            );
        }
    }
    /**
     * Attempts to log a user using the credentials given in
     * parametters.
     *
     * @param string $email    The user's email address.
     * @param string $password The user's password.
     *
     * @return void
     * @throws ExceptionValidationLogin If the user cannot be found or the database connection fails.
     */
    public function login(string $email, string $password): void
    {
        $connection = database::getInstance();
        $stmt = $connection->prepare("SELECT email, hashed_password FROM users WHERE email = :email");
        $stmt->execute([
            'email'=>$email
        ]);
        $hashed_password = $stmt->fetchColumn(1);
        if (!($hashed_password == true && password_verify($password, $hashed_password))){
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
     * @param string $email The email address of the user to fetch.
     *
     * @return void
     *
     * @throws ExceptionFetchDataBD  If the user cannot be found or a database error occurs.
     */
    public function fetchData(string $email): bool
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("
                                SELECT * FROM users
                                JOIN students ON users.user_id = students.student_id
                                JOIN professors ON users.user_id = professors.professor_id
                                JOIN clients ON users.user_id = clients.client_id
                                WHERE users.email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($data)){ return false; }
            foreach($data as $key => $value){
                $this->$key = $value;
            }
            
            //fills in a user_type field MIGHT HAVE TO BE CHANGED // SHOULD BE STORED IN THE DATABASE
            if (isset($this->amu_id)){
                if (isset($this->year)){
                    $this->user_type = "student";
                }
                else {
                    $this->user_type = "professor";
                }
            }
            else {
                $this->user_type = "client";
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
     * @param string $email The email to check for existence.
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
     * Attempts to update a user's password based on it's email
     *
     * @param string $email       The email of the user to update the password of.
     * @param string $newPassword The new password to be updated.
     *
     * @throws ExceptionPasswordUpdateFailed If the update fails or user not found.
     *
     * @return void
     */
    public static function updatePasswordByEmail(string $email, string $newPassword): void
    {
        try {
            $db = database::getInstance();
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password_hash WHERE email = :email");
            if ($stmt->execute(['password_hash' => $hashed_password, 'email' => $email]) && $stmt->rowCount() === 0) {
                throw new ExceptionPasswordUpdateFailed("Aucun utilisateur trouvé avec cet email.");
            }
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour mot de passe: " . $e->getMessage());
            throw new ExceptionPasswordUpdateFailed("Erreur lors de la mise à jour du mot de passe.");
        }
    }
    // Getters.
    /**
     * Returns the amUID of the user.
     *
     * @return string the amUID of the user.
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }
    /**
     * Returns the first name of the user.
     *
     * @return string the first name of the user.
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }
    /**
     * Returns the last name of the user.
     *
     * @return string the last name of the user.
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }
    /**
     * Returns the full name of the user. As [first_name]+[lastName]
     *
     * @return string the full name of the user.
     */
    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    /**
     * Returns the gender of the user.
     *
     * @return string the gender of the user.
     */
    public function getGender(): string
    {
        return $this->gender;
    }
    /**
     * Returns the user type of the user.
     *
     * @return string the user type of the user.
     */
    public function getUserType(): string
    {
        return $this->user_type;
    }
    /**
     * Returns the email of the user.
     *
     * @return string the email of the user.
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    /**
     * Returns the hashed password number of the user.
     *
     * @return string the hashed password number of the user.
     */
    public function getPasswordHash(): string
    {
        return $this->hashed_password;
    }
    /**
     * Returns the phone number of the user.
     *
     * @return string the phone number of the user.
     */
    public function getPhone(): string
    {
        return $this->phone;
    }
    /**
     * Returns the date of birth of the user.
     *
     * @return string the date of birth of the user.
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }
    /**
     * Returns the city of study of the user.
     *
     * @return string the city of study of the user.
     */
    public function getCity(): string
    {
        return $this->city;
    }
    /**
     * Returns the year of study of the user.
     *
     * @return integer|null The year of study of the user.
     */
    public function getYear(): ?int
    {
        return (int) $this->year;
    }
    /**
     * Returns the major of the user.
     *
     * @return string the major of the user.
     */
    public function getParcours(): ?string
    {
        return $this->parcours;
    }
    /**
     * Returns the sub-group of the user.
     *
     * @return string the sub-group of the user.
     */
    public function getTd(): ?string
    {
        return (string) $this->td;
    }
    /**
     * Returns the sub-sub-group of the user.
     *
     * @return string the sub-sub-group of the user.
     */
    public function getTp(): ?string
    {
        return (string) $this->tp;
    }

    /**
     * Returns true if the user is a student.
     *
     * @return boolean is the user a student?
     */
    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }
    /**
     * Returns true if the user is a professor.
     *
     * @return boolean is the user a professor?
     */
    public function isProfessor(): bool
    {
        return $this->user_type === 'professor';
    }
    /**
     * Returns true if the user is a client.
     *
     * @return boolean is the user a client?
     */
    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }
}